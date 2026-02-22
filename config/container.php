<?php

declare(strict_types=1);

use App\Domain\Shared\Clock;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\Shared\Event\InMemoryDomainEventDispatcher;
use App\Domain\Shared\Event\LoggingDomainEventDispatcher;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenEncoder;
use App\Domain\Token\TokenVerifier;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\UserRoleRepositoryInterface;
use App\Domain\User\RoleCatalog;
use App\Integration\Helper\ImageStorage;
use App\Integration\Casbin\CasbinRuleRepository;
use App\Integration\Casbin\DoctrineAdapter;
use App\Integration\Http\NotFoundHandler;
use App\Integration\Logger\LoggerFactory;
use App\Integration\Middleware\CasbinAuthorizationMiddleware;
use App\Integration\Middleware\InternalSignatureMiddleware;
use App\Integration\Middleware\JwtAuthMiddleware;
use App\Integration\Middleware\LoginRateLimitMiddleware;
use App\Integration\Middleware\RefreshRateLimitMiddleware;
use App\Integration\Repository\Doctrine\RefreshTokenRepository;
use App\Integration\Repository\Doctrine\UserRepository;
use App\Integration\Repository\Doctrine\UserRoleRepository;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

return [
    'settings' => static function (): array {
        return require __DIR__ . '/settings.php';
    },

    EntityManagerInterface::class => static function (ContainerInterface $container): EntityManagerInterface {
        $settings = $container->get('settings')['doctrine'] ?? [];
        $metaDirs = (array) ($settings['metadata_dirs'] ?? []);
        $config = ORMSetup::createXMLMetadataConfiguration(
            $metaDirs,
            (bool) ($settings['dev_mode'] ?? false)
        );

        $cacheSettings = (array) ($settings['cache'] ?? []);
        if (!empty($cacheSettings['enabled'])) {
            $namespace = (string) ($cacheSettings['namespace'] ?? 'doctrine');
            $cacheDir = (string) ($cacheSettings['dir'] ?? '');
            $cache = new FilesystemAdapter($namespace, 0, $cacheDir !== '' ? $cacheDir : null);
            $config->setMetadataCache($cache);
            $config->setQueryCache($cache);
            $config->setResultCache($cache);
        }

        if (!empty($settings['cache_dir'])) {
            $config->setProxyDir(rtrim((string) $settings['cache_dir'], '/'));
            $config->setAutoGenerateProxyClasses(true);
        }

        $connection = DriverManager::getConnection((array) ($settings['connection'] ?? []));
        $entityManager = new EntityManager($connection, $config);

        if (!empty($settings['auto_generate_schema'])) {
            $schemaTool = new SchemaTool($entityManager);
            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

            if ($metadata !== []) {
                $schemaTool->updateSchema($metadata);
            }
        }

        return $entityManager;
    },

    CacheInterface::class => static function (ContainerInterface $container): CacheInterface {
        $settings = (array) ($container->get('settings')['cache'] ?? []);
        $cacheDir = (string) ($settings['dir'] ?? '');

        return new FilesystemAdapter('app', 0, $cacheDir !== '' ? $cacheDir : null);
    },

    CasbinRuleRepository::class => static fn(ContainerInterface $container): CasbinRuleRepository => new CasbinRuleRepository(
        $container->get(EntityManagerInterface::class)
    ),

    DoctrineAdapter::class => static fn(ContainerInterface $container): DoctrineAdapter => new DoctrineAdapter(
        $container->get(CasbinRuleRepository::class)
    ),


    CasbinAuthorizationMiddleware::class => static function (ContainerInterface $container): CasbinAuthorizationMiddleware {
        return new CasbinAuthorizationMiddleware(
            $container->get(Enforcer::class),
            $container->get(ResponseFactoryInterface::class),
            'api'
        );
    },

    JwtAuthMiddleware::class => static fn(ContainerInterface $container): JwtAuthMiddleware => new JwtAuthMiddleware(
        $container->get(TokenVerifier::class),
        $container->get(ResponseFactoryInterface::class)
    ),

    InternalSignatureMiddleware::class => static function (ContainerInterface $container): InternalSignatureMiddleware {
        $secret = $_ENV['INTERNAL_HMAC_SECRET'] ?? $_SERVER['INTERNAL_HMAC_SECRET'] ?? '';

        return new InternalSignatureMiddleware(
            (string) $secret,
            $container->get(ResponseFactoryInterface::class),
            (int) ($_ENV['INTERNAL_HMAC_MAX_SKEW'] ?? 60)
        );
    },

    LoginRateLimitMiddleware::class => static function (ContainerInterface $container): LoginRateLimitMiddleware {
        $limit = (int) ($_ENV['AUTH_LOGIN_RATE_LIMIT'] ?? 10);
        $ttl = (int) ($_ENV['AUTH_LOGIN_RATE_TTL'] ?? 60);

        return new LoginRateLimitMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $limit,
            $ttl
        );
    },

    RefreshRateLimitMiddleware::class => static function (ContainerInterface $container): RefreshRateLimitMiddleware {
        $limit = (int) ($_ENV['AUTH_REFRESH_RATE_LIMIT'] ?? 60);
        $ttl = (int) ($_ENV['AUTH_REFRESH_RATE_TTL'] ?? 60);

        return new RefreshRateLimitMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $limit,
            $ttl
        );
    },

    Enforcer::class => static function (ContainerInterface $container): Enforcer {
        $settings = (array) ($container->get('settings')['casbin'] ?? []);
        $modelPath = (string) ($settings['model_path'] ?? __DIR__ . '/../config/casbin/model.conf');

        $adapter = $container->get(DoctrineAdapter::class);

        return new Enforcer($modelPath, $adapter);
    },


    LoggerFactory::class => static fn(ContainerInterface $container): LoggerFactory => new LoggerFactory(
        $container->get('settings')['logger']
    ),

    Clock::class => static fn(): Clock => new Clock(),

    DomainEventDispatcherInterface::class => static function (ContainerInterface $container): DomainEventDispatcherInterface {
        $innerDispatcher = new InMemoryDomainEventDispatcher();

        return new LoggingDomainEventDispatcher(
            $container->get(EntityManagerInterface::class),
            $innerDispatcher,
            $container->get(Clock::class),
        );
    },

    TranslatorInterface::class => static function (): TranslatorInterface {
        $defaultLocale = 'en';
        $translator = new Translator($defaultLocale);
        $translator->addLoader('json', new JsonFileLoader());

        $translator->setFallbackLocales([$defaultLocale]);

        return $translator;
    },

    TranslatorBagInterface::class => static fn(ContainerInterface $container): TranslatorBagInterface =>
        $container->get(TranslatorInterface::class),

    ValidatorInterface::class => static function (ContainerInterface $container): ValidatorInterface {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setTranslator($container->get(TranslatorInterface::class))
            ->setTranslationDomain('messages')
            ->getValidator();
    },



    RoleCatalog::class => static function (ContainerInterface $container): RoleCatalog {
        $authSettings = (array) ($container->get('settings')['auth'] ?? []);
        $roles = array_values(array_unique(array_map(
            static fn(string $role): string => strtolower(trim($role)),
            (array) ($authSettings['roles'] ?? ['user', 'customer', 'admin', 'super_admin'])
        )));
        $defaultRole = strtolower(trim((string) ($authSettings['default_role'] ?? 'user')));
        if ($defaultRole === '') {
            $defaultRole = 'user';
        }

        if (!in_array($defaultRole, $roles, true)) {
            $roles[] = $defaultRole;
        }

        return new RoleCatalog($roles, $defaultRole);
    },

    TokenEncoder::class => static function (ContainerInterface $container): TokenEncoder {
        $secret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? $_ENV['TOKEN_SECRET'] ?? $_SERVER['TOKEN_SECRET'] ?? null;
        if ($secret === null || $secret === '') {
            $secret = 'dev-secret-key';
        }

        $algorithm = $_ENV['TOKEN_ALGORITHM'] ?? 'sha256';

        return new TokenEncoder($secret, $algorithm);
    },

    TokenVerifier::class => static fn(ContainerInterface $container): TokenVerifier => new TokenVerifier(
        $container->get(TokenEncoder::class),
        $container->get(Clock::class)
    ),


    RefreshTokenRepositoryInterface::class => static fn(ContainerInterface $container): RefreshTokenRepositoryInterface => new RefreshTokenRepository(
        $container->get(EntityManagerInterface::class)
    ),

    UserRepositoryInterface::class => static fn(ContainerInterface $container): UserRepositoryInterface => new UserRepository(
        $container->get(EntityManagerInterface::class)
    ),

    UserRoleRepositoryInterface::class => static fn(ContainerInterface $container): UserRoleRepositoryInterface => new UserRoleRepository(
        $container->get(EntityManagerInterface::class)
    ),


    ImageStorage::class => static function (ContainerInterface $container): ImageStorage {
        $config = (array) ($container->get('settings')['uploads']['ads'] ?? []);
        $path = (string) ($config['path'] ?? (__DIR__ . '/../public/uploads/ads'));
        $publicPrefix = (string) ($config['public_prefix'] ?? '/uploads/ads/');

        return new ImageStorage($path, $publicPrefix);
    },

    App::class => static function (ContainerInterface $container): App {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },

    ResponseFactoryInterface::class => static fn(): ResponseFactoryInterface => new ResponseFactory(),

    ErrorMiddleware::class => static function (ContainerInterface $container): ErrorMiddleware {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];
        $logger = $container->get(LoggerFactory::class)
            ->addFileHandler($settings['log_file'] ?? 'error.log')
            ->createLogger();

        $middleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool) $settings['display_error_details'],
            (bool) $settings['log_errors'],
            (bool) $settings['log_error_details'],
            $logger
        );

        $middleware->setErrorHandler(
            HttpNotFoundException::class,
            new NotFoundHandler(
                $container->get(ResponseFactoryInterface::class),
            ),
        );

        return $middleware;
    },
];
