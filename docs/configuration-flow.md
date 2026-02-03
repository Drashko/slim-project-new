# Configuration and Container Flow

This document explains how the application bootstraps configuration, feeds it into the dependency injection container, and exposes that configuration to the rest of the stack.

## Bootstrapping overview

1. **`config/bootstrap.php`** builds a PHP-DI container from the definitions in `config/container.php`, then resolves the Slim `App` instance and its middleware pipeline.
2. The container definition references `config/settings.php` via a lazy `settings` entry, so every service can read the merged configuration array.
3. Once the container is built, the configured services (logger, database, localization, RBAC, etc.) are available to routes and middleware through type-hinted dependencies.

## Settings sources and normalization

`config/settings.php` loads environment variables with `vlucas/phpdotenv`, then normalizes values so downstream services receive consistent types:

- **Boolean parsing**: `APP_DEBUG`, `DB_AUTO_SYNC`, and similar flags accept truthy strings like `"yes"` or `"1"` and are coerced to booleans.
- **Database defaults**: Doctrine settings normalize the driver name (e.g., `mariadb` → `pdo_mysql`) and sanitize server versions before constructing the connection options.
- **Localization defaults**: A default locale (`en`) and supported locales map load translation files from `translations/*.json`, while leaving room for route path localization.
- **Cache paths**: Container, route, and Doctrine cache directories are resolved to absolute paths so they can be created early in the bootstrap process.

The returned associative array supplies sub-configurations for `session`, `error`, `logger`, `route_cache`, `container`, `doctrine`, `localization`, and `rbac`. Each section mirrors the needs of its dependent services—for example, `rbac` describes role inheritance and permissions.

### Example environment overrides

Use the same pattern as the route and template cache toggles to enable DI and Doctrine caches:

```dotenv
# Routes
ROUTE_CACHE_ENABLED=1
ROUTE_CACHE_PATH=tmp/var/routes.cache.php

# DI container
DI_CACHE_ENABLED=1
DI_CACHE_DIR=tmp/var/container
DI_PROXY_ENABLED=1
DI_PROXY_DIR=tmp/var/container/proxies

# Doctrine
DOCTRINE_CACHE_ENABLED=1
DOCTRINE_CACHE_DIR=tmp/var/doctrine/cache
DOCTRINE_CACHE_NAMESPACE=slim_access_control
DOCTRINE_PROXY_DIR=tmp/var/doctrine/proxies
```

## Container assembly

`config/container.php` wires services using the `settings` array and registers factories for key integrations:

- **Doctrine EntityManager**: Builds metadata configuration from configured domains, optionally auto-generating the schema in non-production environments.
- **Session and flash**: Starts a PHP session via `Odan\Session` using the configured session name, then exposes `Messages` for flash storage.
- **Logging**: Instantiates a `LoggerFactory` seeded with file path, filename, and log level from the `logger` settings.
- **Localization**: Creates a `Translator` with JSON loaders so routes and middleware can render locale-aware responses.
- **Forms, CSRF, and validation**: Wires Symfony form and CSRF components with the shared translator-backed validator for consistent error messages.
- **RBAC policy**: Hydrates a Laminas RBAC graph from the `rbac.roles` map, normalizing role names and permissions before exposing them through a `Policy` service.
- **Slim app and middleware**: Provides the Slim `App`, `ResponseFactory`, and `ErrorMiddleware` using the settings that control error display and logging.
- **Doctrine caches**: When enabled, the container wires Symfony cache pools into Doctrine's metadata, query, and result caches, while keeping proxy classes in the configured proxy cache directory.
- **DI container caching**: `config/bootstrap.php` preloads the settings file to enable PHP-DI compilation and proxy caching before the container is built.

Because each factory pulls from the centralized `settings` entry, updating environment variables or the settings file flows automatically into the container without additional wiring.
