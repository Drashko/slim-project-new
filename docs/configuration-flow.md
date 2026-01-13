# Configuration and Container Flow

This document explains how the application bootstraps configuration, feeds it into the dependency injection container, and exposes that configuration to the rest of the stack.

## Bootstrapping overview

1. **`config/bootstrap.php`** builds a PHP-DI container from the definitions in `config/container.php`, then resolves the Slim `App` instance and its middleware pipeline.
2. The container definition references `config/settings.php` via a lazy `settings` entry, so every service can read the merged configuration array.
3. Once the container is built, the configured services (logger, database, templating, localization, RBAC, etc.) are available to routes, middleware, and templates through type-hinted dependencies.

## Settings sources and normalization

`config/settings.php` loads environment variables with `vlucas/phpdotenv`, then normalizes values so downstream services receive consistent types:

- **Boolean parsing**: `APP_DEBUG`, `DB_AUTO_SYNC`, and similar flags accept truthy strings like `"yes"` or `"1"` and are coerced to booleans.
- **Database defaults**: Doctrine settings normalize the driver name (e.g., `mariadb` → `pdo_mysql`) and sanitize server versions before constructing the connection options.
- **React asset paths**: `REACT_ASSET_BUILD_PATH` is resolved to an absolute path, and `REACT_ASSET_PUBLIC_PREFIX` is normalized to a trailing-slash URL segment for the Plates helper functions.
- **Localization defaults**: A default locale (`en`) and supported locales map load translation files from `translations/*.json`, while leaving room for route path localization.

The returned associative array supplies sub-configurations for `session`, `error`, `logger`, `templates`, `doctrine`, `localization`, `rbac`, and `react`. Each section mirrors the needs of its dependent services—for example, the `templates` block sets the Plates base path and file extension, and `rbac` describes role inheritance and permissions.

## Container assembly

`config/container.php` wires services using the `settings` array and registers factories for key integrations:

- **Doctrine EntityManager**: Builds metadata configuration from configured domains, optionally auto-generating the schema in non-production environments.
- **Session and flash**: Starts a PHP session via `Odan\Session` using the configured session name, then exposes `Messages` for flash storage.
- **Logging**: Instantiates a `LoggerFactory` seeded with file path, filename, and log level from the `logger` settings.
- **Localization**: Creates a `Translator` with JSON loaders and a `PathLocalizer` that understands locale-specific route prefixes.
- **Forms, CSRF, and validation**: Wires Symfony form and CSRF components with the shared translator-backed validator for consistent error messages.
- **RBAC policy**: Hydrates a Laminas RBAC graph from the `rbac.roles` map, normalizing role names and permissions before exposing them through a `Policy` service.
- **View engine**: Configures the Plates `Engine` with template folders, shared data (flash messages), localization helpers, React asset helpers, and RBAC template extensions.
- **Slim app and middleware**: Provides the Slim `App`, `ResponseFactory`, and `ErrorMiddleware` using the settings that control error display and logging.

Because each factory pulls from the centralized `settings` entry, updating environment variables or the settings file flows automatically into the container without additional wiring.
