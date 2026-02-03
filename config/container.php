<?php

declare(strict_types=1);

use App\Domain\Ad\AdRepositoryInterface;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\Shared\Event\InMemoryDomainEventDispatcher;
use App\Domain\Shared\Event\LoggingDomainEventDispatcher;
use App\Domain\Token\RefreshTokenRepositoryInterface;
use App\Domain\Token\TokenEncoder;
use App\Domain\Token\TokenVerifier;
use App\Domain\User\UserRepositoryInterface;
use App\Integration\Helper\ImageStorage;
use App\Integration\Http\NotFoundHandler;
use App\Integration\Logger\LoggerFactory;
use App\Integration\Middleware\LocalizationMiddleware;
use App\Integration\Repository\Doctrine\AdRepository;
use App\Integration\Repository\Doctrine\CategoryRepository;
use App\Integration\Repository\Doctrine\RefreshTokenRepository;
use App\Integration\Repository\Doctrine\UserRepository;
use App\Web\API\Controller\LocalizationController;
use App\Web\Shared\Paginator;
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

    Paginator::class => static function (): Paginator {
        return new Paginator();
    },

    LocalizationController::class => static function (ContainerInterface $container): LocalizationController {
        return new LocalizationController((array) $container->get('settings'));
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

    TranslatorInterface::class => static function (ContainerInterface $container): TranslatorInterface {
        $settings = $container->get('settings')['localization'] ?? [];
        $defaultLocale = (string) ($settings['default_locale'] ?? 'en');
        $translator = new Translator($defaultLocale);
        $translator->addLoader('json', new JsonFileLoader());

        $paths = $settings['paths'] ?? [];
        foreach ($paths as $locale => $path) {
            if (!is_string($path) || $path === '' || !is_file($path)) {
                continue;
            }

            $translator->addResource('json', $path, (string) $locale);
        }

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

    LocalizationMiddleware::class => static function (ContainerInterface $container): LocalizationMiddleware {
        $settings = $container->get('settings')['localization'] ?? [];

        return new LocalizationMiddleware(
            $container->get(TranslatorInterface::class),
            (array) ($settings['supported_locales'] ?? ['en' => 'English']),
            (string) ($settings['default_locale'] ?? 'en')
        );
    },

    TokenEncoder::class => static function (ContainerInterface $container): TokenEncoder {
        $secret = $_ENV['TOKEN_SECRET'] ?? $_SERVER['TOKEN_SECRET'] ?? null;
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


    AdRepositoryInterface::class => static fn(ContainerInterface $container): AdRepositoryInterface => new AdRepository(
        $container->get(EntityManagerInterface::class)
    ),

    CategoryRepositoryInterface::class => static fn(ContainerInterface $container): CategoryRepositoryInterface => new CategoryRepository(
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
                array_change_key_case(
                    (array) ($container->get('settings')['localization']['supported_locales'] ?? []),
                    CASE_LOWER
                ),
            ),
        );

        return $middleware;
    },
];
