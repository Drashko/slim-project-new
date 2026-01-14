<?php

declare(strict_types=1);

use App\Domain\Auth\RefreshTokenRepositoryInterface;
use App\Domain\Auth\TokenEncoder;
use App\Domain\Auth\TokenVerifier;
use App\Domain\Ad\AdRepositoryInterface;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Domain\Role\RoleRepositoryInterface;
use App\Domain\Shared\Event\DomainEventDispatcherInterface;
use App\Domain\Shared\Event\InMemoryDomainEventDispatcher;
use App\Domain\Shared\Event\LoggingDomainEventDispatcher;
use App\Domain\Shared\Clock;
use App\Domain\User\UserRepositoryInterface;
use App\Integration\Logger\LoggerFactory;
use App\Integration\Helper\ImageStorage;
use App\Integration\Middleware\LocalizationMiddleware;
use App\Integration\Rbac\Policy;
use App\Integration\Repository\Doctrine\PermissionRepository;
use App\Integration\Repository\Doctrine\RefreshTokenRepository;
use App\Integration\Repository\Doctrine\RoleRepository;
use App\Integration\Repository\Doctrine\AdRepository;
use App\Integration\Repository\Doctrine\CategoryRepository;
use App\Integration\Repository\Doctrine\UserRepository;
use App\Integration\Routing\PathLocalizer;
use App\Integration\View\Plates\RbacExtension;
use App\Integration\View\Plates\ReactExtension;
use App\Integration\View\Plates\ViteExtension;
use App\Integration\View\TemplateRenderer;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Laminas\Permissions\Rbac\Rbac as LaminasRbac;
use Laminas\Permissions\Rbac\Role as LaminasRole;
use League\Plates\Engine;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Rbac\LaminasRbac as MezzioLaminasRbac;
use Odan\Session\PhpSession;
use Odan\Session\SessionInterface;
use Odan\Session\SessionManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenStorage\NativeSessionTokenStorage;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

return [
    'settings' => static function (): array {
        return require __DIR__ . '/settings.php';
    },

    EntityManagerInterface::class => static function (ContainerInterface $container): EntityManagerInterface {
        $settings = $container->get('settings')['doctrine'] ?? [];
        $metaDirs = (array) ($settings['metadata_dirs'] ?? []);
        $config = ORMSetup::createAttributeMetadataConfiguration(
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

    SessionInterface::class => static function (ContainerInterface $container): SessionInterface {
        $session = new PhpSession($container->get('settings')['session']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $session->start();
        }

        return $session;
    },

    SessionManagerInterface::class => static function (ContainerInterface $container): SessionInterface {
        return $container->get(SessionInterface::class);
    },

    Messages::class => static function (ContainerInterface $container): Messages {
        // Ensure the PHP session is active before creating flash messages
        $container->get(SessionInterface::class);

        return new Messages();
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

    PathLocalizer::class => static function (ContainerInterface $container): PathLocalizer {
        $localization = $container->get('settings')['localization'] ?? [];

        return new PathLocalizer((array) ($localization['route_paths'] ?? []));
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

    CsrfTokenManagerInterface::class => static fn(): CsrfTokenManagerInterface => new CsrfTokenManager(
        null,
        new NativeSessionTokenStorage()
    ),

    FormFactoryInterface::class => static function (ContainerInterface $container): FormFactoryInterface {
        return Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension($container->get(CsrfTokenManagerInterface::class)))
            ->addExtension(new ValidatorExtension($container->get(ValidatorInterface::class)))
            ->getFormFactory();
    },

    LocalizationMiddleware::class => static function (ContainerInterface $container): LocalizationMiddleware {
        $settings = $container->get('settings')['localization'] ?? [];

        return new LocalizationMiddleware(
            $container->get(TranslatorInterface::class),
            $container->get(SessionInterface::class),
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

    LaminasRbac::class => static function (ContainerInterface $container): LaminasRbac {
        $settings = (array) $container->get('settings');
        $rbacSettings = (array) ($settings['rbac'] ?? []);
        $roleDefinitions = (array) ($rbacSettings['roles'] ?? []);

        $normalizeRole = static function (mixed $role): ?string {
            if (!is_string($role)) {
                return null;
            }
            $role = strtoupper(trim($role));
            return $role === '' ? null : $role;
        };

        $normalizeAbility = static function (mixed $ability): ?string {
            if (!is_string($ability)) {
                return null;
            }
            $ability = strtolower(trim($ability));
            return $ability === '' ? null : $ability;
        };

        $rbac = new LaminasRbac();
        $rbac->setCreateMissingRoles(true);

        foreach ($roleDefinitions as $name => $definition) {
            $roleName = $normalizeRole($name);
            if ($roleName === null) {
                continue;
            }

            $role = $rbac->hasRole($roleName) ? $rbac->getRole($roleName) : new LaminasRole($roleName);

            $permissions = [];
            foreach ((array) ($definition['permissions'] ?? []) as $permission) {
                $normalizedPermission = $normalizeAbility($permission);
                if ($normalizedPermission !== null) {
                    $permissions[$normalizedPermission] = true;
                }
            }

            foreach (array_keys($permissions) as $permission) {
                $role->addPermission($permission);
            }

            $parents = [];
            foreach ((array) ($definition['children'] ?? []) as $parentRole) {
                $normalizedParent = $normalizeRole($parentRole);
                if ($normalizedParent !== null) {
                    $parents[] = $normalizedParent;
                }
            }

            $rbac->addRole($role, $parents === [] ? null : $parents);
        }

        return $rbac;
    },

    AuthorizationInterface::class => static function (ContainerInterface $container): AuthorizationInterface {
        return new MezzioLaminasRbac($container->get(LaminasRbac::class));
    },

    Policy::class => static fn(ContainerInterface $container): Policy => new Policy(
        $container->get(LaminasRbac::class)
    ),

    RefreshTokenRepositoryInterface::class => static fn(ContainerInterface $container): RefreshTokenRepositoryInterface => new RefreshTokenRepository(
        $container->get(EntityManagerInterface::class)
    ),

    UserRepositoryInterface::class => static fn(ContainerInterface $container): UserRepositoryInterface => new UserRepository(
        $container->get(EntityManagerInterface::class)
    ),

    RoleRepositoryInterface::class => static fn(ContainerInterface $container): RoleRepositoryInterface => new RoleRepository(
        $container->get(EntityManagerInterface::class)
    ),

    PermissionRepositoryInterface::class => static fn(ContainerInterface $container): PermissionRepositoryInterface => new PermissionRepository(
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

    Engine::class => static function (ContainerInterface $container): Engine {
        $settings = $container->get('settings')['templates'];
        $engine = new Engine($settings['path'], $settings['extension'] ?? 'php');

        $engine
            ->addFolder('layout', $settings['path'] . '/layout')
            ->addFolder('front', $settings['path'] . '/front')
            ->addFolder('auth', $settings['path'] . '/auth')
            ->addFolder('admin', $settings['path'] . '/admin')
            ->addFolder('profile', $settings['path'] . '/profile');

        $engine->addData([
            'flash' => $container->get(Messages::class),
        ]);

        $translator = $container->get(TranslatorInterface::class);
        $localization = $container->get('settings')['localization'] ?? [];
        $supportedLocales = (array) ($localization['supported_locales'] ?? ['en' => 'English']);
        $pathLocalizer = $container->get(PathLocalizer::class);

        $engine->registerFunction('trans', function (
            string $id,
            array $parameters = [],
            ?string $domain = null,
            ?string $locale = null
        ) use ($translator): string {
            return $translator->trans($id, $parameters, $domain, $locale);
        });

        $engine->registerFunction('current_locale', static function () use ($translator): string {
            return $translator->getLocale();
        });

        $engine->registerFunction('available_locales', static function () use ($supportedLocales): array {
            return $supportedLocales;
        });

        $engine->registerFunction('locale_name', static function (string $locale) use ($supportedLocales): string {
            return $supportedLocales[$locale] ?? $locale;
        });

        $engine->registerFunction('locale_url', static function (?string $path = null, ?string $locale = null, ?string $scope = null) use ($supportedLocales, $translator, $container, $pathLocalizer): string {
            $normalizeLocale = static function (mixed $value) use ($supportedLocales): ?string {
                if (!is_string($value) || $value === '') {
                    return null;
                }

                $normalized = strtolower(str_replace('_', '-', $value));
                if (array_key_exists($normalized, $supportedLocales)) {
                    return $normalized;
                }

                $short = substr($normalized, 0, 2);
                if ($short !== '' && array_key_exists($short, $supportedLocales)) {
                    return $short;
                }

                return null;
            };

            $scopeKey = null;
            if ($scope === 'admin') {
                $scopeKey = 'locale_admin';
            } elseif ($scope === 'public') {
                $scopeKey = 'locale_public';
            }

            $scopedLocale = null;
            if ($scopeKey !== null && $container->has(SessionInterface::class)) {
                $sessionLocale = $container->get(SessionInterface::class)->get($scopeKey);
                $scopedLocale = $normalizeLocale($sessionLocale);
            }

            $targetLocale = $normalizeLocale($locale);
            if ($targetLocale === null && $scopedLocale !== null) {
                $targetLocale = $scopedLocale;
            }

            if ($targetLocale === null) {
                $targetLocale = $normalizeLocale($translator->getLocale());
            }

            if ($targetLocale === null) {
                $targetLocale = $normalizeLocale(array_key_first($supportedLocales));
            }

            if ($targetLocale === null) {
                $targetLocale = 'en';
            }

            $normalizedPath = $path ?? '';
            $normalizedPath = trim($normalizedPath);

            if ($normalizedPath === '' || $normalizedPath === '/') {
                return '/' . $targetLocale;
            }

            $translatedPath = $pathLocalizer->prefix($normalizedPath, $targetLocale);

            if ($translatedPath === '') {
                return '/' . $targetLocale;
            }

            return $translatedPath;
        });

        $engine->registerFunction('locale_switch_url', static function (string $locale) use ($supportedLocales, $pathLocalizer): string {
            if (!array_key_exists($locale, $supportedLocales)) {
                return '#';
            }

            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $parts = parse_url($requestUri);
            $path = isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/';
            $segments = array_values(array_filter(
                explode('/', trim($path, '/')),
                static fn(string $segment): bool => $segment !== ''
            ));

            if ($segments !== []) {
                $candidate = strtolower($segments[0]);
                if (array_key_exists($candidate, $supportedLocales)) {
                    array_shift($segments);
                }
            }

            $newPath = '/' . $locale;
            $remainingPath = implode('/', $segments);
            $canonicalPath = $pathLocalizer->canonicalize($remainingPath);
            $newPath = $pathLocalizer->prefix($canonicalPath, $locale);

            $queryParams = [];
            if (!empty($parts['query'])) {
                parse_str((string) $parts['query'], $queryParams);
            }

            unset($queryParams['lang']);
            $queryString = http_build_query($queryParams);

            if ($queryString !== '') {
                $newPath .= '?' . $queryString;
            }

            return $newPath;
        });

        $settings = (array) $container->get('settings');
        $reactSettings = (array) ($settings['react'] ?? []);
        $reactEntry = (string) ($reactSettings['entry'] ?? 'src/main.jsx');
        $reactManifest = (string) ($reactSettings['manifest_path'] ?? '');
        $reactPublicPrefix = (string) ($reactSettings['public_prefix'] ?? '/assets/react/');
        $reactDevServer = trim((string) ($reactSettings['dev_server'] ?? ''));

        $engine->loadExtension(new ReactExtension(
            $reactEntry,
            $reactManifest,
            $reactPublicPrefix,
            $reactDevServer
        ));

        $adminSettings = (array) ($settings['admin_assets'] ?? []);
        $engine->loadExtension(new ViteExtension([
            'admin' => [
                'entry' => (string) ($adminSettings['entry'] ?? 'resources/admin/main.js'),
                'manifest_path' => (string) ($adminSettings['manifest_path'] ?? ''),
                'public_prefix' => (string) ($adminSettings['public_prefix'] ?? '/assets/admin/'),
                'dev_server' => trim((string) ($adminSettings['dev_server'] ?? '')),
            ],
        ]));

        $engine->loadExtension(new RbacExtension(
            $container->get(Policy::class),
            $container->get(SessionInterface::class)
        ));

        return $engine;
    },

    TemplateRenderer::class => static function (ContainerInterface $container): TemplateRenderer {
        $settings = (array) $container->get('settings');
        $templateSettings = (array) ($settings['templates'] ?? []);
        $cacheSettings = (array) ($templateSettings['cache'] ?? []);
        $cachePool = null;

        if (!empty($cacheSettings['enabled']) && !empty($cacheSettings['path'])) {
            $cachePath = (string) $cacheSettings['path'];
            if (!is_dir($cachePath)) {
                mkdir($cachePath, 0775, true);
            }
            $cachePool = new FilesystemAdapter('templates', 0, $cachePath);
        }

        return new TemplateRenderer(
            $container->get(Engine::class),
            $cachePool instanceof CacheItemPoolInterface ? $cachePool : null,
            $cacheSettings
        );
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

        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool) $settings['display_error_details'],
            (bool) $settings['log_errors'],
            (bool) $settings['log_error_details'],
            $logger
        );
    },
];
