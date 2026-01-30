<?php

declare(strict_types=1);

use App\Integration\System\AppEnvironment;
use Dotenv\Dotenv;
use Monolog\Level;

(Dotenv::createImmutable(__DIR__ . '/../'))->safeLoad();

$projectRoot = dirname(__DIR__);

$boolean = static function (mixed $value): bool {
    if (in_array($value, ['true', 1, '1', true, 'yes'], true)) {
        return true;
    }

    return false;
};

$normalizeDoctrineDriver = static function (?string $driver): string {
    if ($driver === null || $driver === '') {
        return 'pdo_mysql';
    }

    $map = [
        'mariadb' => 'pdo_mysql',
        'mysql' => 'pdo_mysql',
        'pdo_mariadb' => 'pdo_mysql',
    ];

    $normalized = strtolower($driver);

    return $map[$normalized] ?? $normalized;
};

$normalizeServerVersion = static function (?string $version): ?string {
    if ($version === null) {
        return null;
    }

    $version = trim($version);

    if ($version === '') {
        return null;
    }

    if (preg_match('/^(?P<prefix>.*?)(?P<major>\d+)\.(?P<minor>\d+)(?:\.(?P<patch>\d+))?(?P<suffix>.*)$/', $version, $matches) === 1) {
        $patch = ($matches['patch'] ?? '') !== '' ? $matches['patch'] : '0';

        return sprintf(
            '%s%s.%s.%s%s',
            $matches['prefix'],
            $matches['major'],
            $matches['minor'],
            $patch,
            $matches['suffix']
        );
    }

    return $version;
};

$normalizePublicPrefix = static function (?string $prefix, string $fallback): string {
    if ($prefix === null) {
        return $fallback;
    }

    $normalized = trim(str_replace('\\', '/', $prefix));

    if ($normalized === '') {
        return $fallback;
    }

    return rtrim($normalized, '/') . '/';
};

$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'prod';
$environment = new AppEnvironment($appEnv);

$appSnakeName = strtolower(str_replace(' ', '_', $_ENV['APP_NAME'] ?? 'slim_app'));

$resolveBuildPath = static function (string $path) use ($projectRoot): string {
    if ($path === '') {
        return $projectRoot;
    }

    if (preg_match('#^(?:[a-zA-Z]:[\\/]|\\\\|/)#', $path) === 1) {
        return $path;
    }

    return rtrim($projectRoot, '/\\') . '/' . ltrim(str_replace('\\', '/', $path), '/');
};

$assetBuildPath = $resolveBuildPath($_ENV['ASSET_BUILD_PATH'] ?? 'public/assets');
$assetPublicPrefix = $normalizePublicPrefix($_ENV['ASSET_PUBLIC_PREFIX'] ?? '/assets/', '/assets/');
$assetDevServer = $environment->isProduction()
    ? ''
    : rtrim((string) ($_ENV['ASSET_DEV_SERVER'] ?? ''), '/');
$defaultCacheDir = $resolveBuildPath($_ENV['APP_CACHE_DIR'] ?? 'tmp/var');
$cacheEnabled = !$environment->isDevelopment();

error_reporting(E_ALL);
ini_set('display_errors', $boolean($_ENV['APP_DEBUG'] ?? 0));

return [
    'public' => __DIR__ . '/../public',
    'error' => [
        'display_error_details' => $boolean($_ENV['APP_DEBUG'] ?? 0),
        'log_errors' => true,
        'log_error_details' => true,
        'log_file' => 'error.log',
    ],
    'logger' => [
        'name' => 'app',
        'path' => __DIR__ . '/../logs',
        'filename' => 'app.log',
        'level' => Level::Debug,
        'file_permission' => 0775,
    ],
    'templates' => [
        'path' => __DIR__ . '/../templates',
        'extension' => 'php',
        'cache' => [
            'enabled' => $boolean($_ENV['TEMPLATE_CACHE_ENABLED'] ?? ($cacheEnabled ? 'true' : 'false')),
            'path' => $resolveBuildPath($_ENV['TEMPLATE_CACHE_DIR'] ?? ($defaultCacheDir . '/templates')),
            'ttl' => (int) ($_ENV['TEMPLATE_CACHE_TTL'] ?? 900),
        ],
    ],
    'route_cache' => [
        'enabled' => $boolean($_ENV['ROUTE_CACHE_ENABLED'] ?? ($cacheEnabled ? 'true' : 'false')),
        'path' => $resolveBuildPath($_ENV['ROUTE_CACHE_PATH'] ?? ($defaultCacheDir . '/routes.cache.php')),
    ],
    'container' => [
        'cache' => [
            'enabled' => $boolean($_ENV['DI_CACHE_ENABLED'] ?? ($cacheEnabled ? 'true' : 'false')),
            'path' => $resolveBuildPath($_ENV['DI_CACHE_DIR'] ?? ($defaultCacheDir . '/container')),
        ],
        'proxies' => [
            'enabled' => $boolean($_ENV['DI_PROXY_ENABLED'] ?? ($cacheEnabled ? 'true' : 'false')),
            'path' => $resolveBuildPath($_ENV['DI_PROXY_DIR'] ?? ($defaultCacheDir . '/container/proxies')),
        ],
    ],
    'doctrine' => [
        'dev_mode' => $environment->isDevelopment(),
        'cache_dir' => $resolveBuildPath($_ENV['DOCTRINE_PROXY_DIR'] ?? ($defaultCacheDir . '/doctrine/proxies')),
        'cache' => [
            'enabled' => $boolean($_ENV['DOCTRINE_CACHE_ENABLED'] ?? ($cacheEnabled ? 'true' : 'false')),
            'dir' => $resolveBuildPath($_ENV['DOCTRINE_CACHE_DIR'] ?? ($defaultCacheDir . '/doctrine/cache')),
            'namespace' => $_ENV['DOCTRINE_CACHE_NAMESPACE'] ?? $appSnakeName,
        ],
        'metadata_dirs' => [
            __DIR__ . '/../src/Integration/Doctrine/Mapping',
        ],
        'connection' => [
            'driver' => $normalizeDoctrineDriver($_ENV['DB_DRIVER'] ?? null),
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'dbname' => $environment->isTest()
                ? ($_ENV['TEST_DB_NAME'] ?? 'slim_access_test')
                : ($_ENV['DB_NAME'] ?? 'slim_app_test'),
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'serverVersion' => $_ENV['DB_VERSION'] ?? '10.5',
        ],
        'auto_generate_schema' => $boolean($_ENV['DB_AUTO_SYNC'] ?? !$environment->isProduction()),
    ],
    'uploads' => [
        'ads' => [
            'path' => __DIR__ . '/../public/uploads/ads',
            'public_prefix' => '/uploads/ads/',
        ],
    ],
    'localization' => [
        'default_locale' => 'en',
        'supported_locales' => [
            'en' => 'English',
            'bg' => 'Български',
        ],
        'paths' => [
            'en' => __DIR__ . '/../translations/en.json',
            'bg' => __DIR__ . '/../translations/bg.json',
        ],
        'route_paths' => [],
    ],
    'pagination' => [
        'default_per_page' => max(1, (int) ($_ENV['DEFAULT_PER_PAGE'] ?? 10)),
        'admin_users_per_page' => max(1, (int) ($_ENV['ADMIN_USERS_PER_PAGE'] ?? 0)),
    ],
    'react' => [
        'entry' => 'src/main.jsx',
        'build_path' => $assetBuildPath,
        'manifest_path' => rtrim($assetBuildPath, '/\\') . '/manifest.json',
        'public_prefix' => $assetPublicPrefix,
        'dev_server' => $assetDevServer,
    ],
    'admin_react' => [
        'entry' => 'src/admin/react.jsx',
        'build_path' => $assetBuildPath,
        'manifest_path' => rtrim($assetBuildPath, '/\\') . '/manifest.json',
        'public_prefix' => $assetPublicPrefix,
        'dev_server' => $assetDevServer,
    ],
    'admin_assets' => [
        'entry' => 'src/admin/main.js',
        'build_path' => $assetBuildPath,
        'manifest_path' => rtrim($assetBuildPath, '/\\') . '/manifest.json',
        'public_prefix' => $assetPublicPrefix,
        'dev_server' => $assetDevServer,
        'styles' => [
            'src/admin/admin.css',
        ],
    ],
    'public_assets' => [
        'entry' => 'src/public/main.js',
        'build_path' => $assetBuildPath,
        'manifest_path' => rtrim($assetBuildPath, '/\\') . '/manifest.json',
        'public_prefix' => $assetPublicPrefix,
        'dev_server' => $assetDevServer,
    ],
    'commands' => [],
];
