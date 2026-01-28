# AI Coding Assistant Instructions

## Project Overview

**Slim Market Demo** is a Slim 4 authentication and RBAC (Role-Based Access Control) marketplace application using **Domain-Driven Design (DDD)**, **Hexagonal Architecture**, and **Clean Architecture** principles. It features dual-mode rendering (PHP templates + React), multi-locale support, Doctrine ORM, and token-based auth.

## Architecture Layers

### 1. **Domain Layer** (`src/Domain/`)
Core business logic independent of frameworks. Contains **Entities**, **Value Objects**, **Repositories** (interfaces only), and **Domain Events**.

- `User/`, `Role/`, `Permission/`: RBAC aggregates with explicit validation in constructors
- `Token/`: JWT encoding/verification, token claims, refresh token management
- `Ad/`, `Category/`, `Address/`: Marketplace domain entities
- `Shared/Clock`, `DomainEventDispatcher`: Cross-cutting domain concerns

**Pattern**: All entities use UUID (`Symfony\Uid`) and timestamp tracking (`createdAt`, `updatedAt`). Entities enforce invariants in setters via exceptions.

### 2. **Feature Layer** (`src/Feature/`)
Application use cases (commands, queries) orchestrating domain logic. Not yet heavily used; most logic flows through controllers.

### 3. **Integration Layer** (`src/Integration/`)
Framework adapters and external system interfaces:

- `Repository/Doctrine/`: Doctrine ORM implementation of domain repository interfaces
- `Session/`: Odan\Session wrapper for admin/public session separation (different cookies/paths)
- `View/Plates/`: Custom Plates template engine with extensions (Vite, React, RBAC helpers)
- `Middleware/`: Localization, authentication, error handling
- `Rbac/`: Laminas RBAC integration with policy enforcement
- `Http/`: PSR-7 response factories and HTTP concerns
- `Listener/`: Event subscribers (currently logging focus)
- `Logger/`: Monolog-based logging with daily file rotation

### 4. **Web Layer** (`src/Web/`)
HTTP controllers wired to routes. Organized by domain boundary (Admin, Front, API, Shared).

- Controllers receive dependencies via constructor injection
- Controllers are invokable (`__invoke` method)
- Return `ResponseInterface` with rendered template via `TemplateRenderer`

## Critical Developer Workflows

### Local Development Setup

```bash
# Backend
composer install
php -S 0.0.0.0:8080 -t public public/index.php

# Frontend (separate terminal)
npm run dev

# Both serve at localhost:5173 (Vite) & localhost:8080 (PHP)
```

### Running Tests

```bash
composer test
```

Uses PHPUnit configured in `phpunit.xml`. Tests live in `tests/` mirroring `src/` structure.

### Building Frontend Assets

```bash
npm run build
```

Vite (multi-page) bundles into `public/assets/`. Enabled by default on page load via `ASSET_BUILD_PATH` env var. Dev server overridable via `ASSET_DEV_SERVER` env.

### Database & Migrations

Doctrine CLI commands available via `vendor/bin/doctrine`:

```bash
vendor/bin/doctrine orm:schema-tool:create  # Auto-generated in bootstrap if DB_AUTO_SYNC=1
vendor/bin/doctrine migrations:generate     # Create migration
vendor/bin/doctrine migrations:migrate      # Apply migrations
```

## Configuration & Bootstrapping

**Entry point**: `config/bootstrap.php` → builds PHP-DI container → resolves Slim App.

**Settings flow**: `config/settings.php` loads `.env` via `phpdotenv`, normalizes types (booleans, paths, locale maps), returns merged config array.

**Container definitions**: `config/container.php` wires services:
- Doctrine EntityManager (with optional auto-schema generation)
- Slim App, ResponseFactory, ErrorMiddleware
- Logger (Monolog) with daily file rotation
- Session management (public/admin with separate cookies)
- Template engine (Plates with custom extensions)
- RBAC policy (Laminas with role inheritance from settings)
- Caching (container, routes, Doctrine, templates — all optional/configurable)

**Key env vars**:
- `APP_DEBUG`: Error display toggle
- `DB_AUTO_SYNC=1`: Auto-generate Doctrine schema (dev-only)
- `ROUTE_CACHE_ENABLED`, `TEMPLATE_CACHE_ENABLED`: Performance caches
- `ASSET_DEV_SERVER`: Vite dev server URL for template asset resolution
- Locale defaults: `DEFAULT_LOCALE`, supported locales in `translations/*.json`

## Routing & Middleware

**Route registration**: `config/routes.php` uses Slim groups with prefix patterns (e.g., `/{locale:en|bg}` for multi-locale support).

**Middleware stack** (`config/middleware.php`):
1. Session manager (branches admin/public sessions)
2. Localization extractor (from route + session)
3. Body parsing
4. Routing
5. Static asset caching
6. Error handling (catches exceptions, returns JSON/HTML)

**Pattern**: All public routes prefixed with locale. Redirects handle locale switching.

## Data Flow & Domain Events

**Entity lifecycle**: Constructor initializes with UUID + timestamp. Setters call `touch()` to update `updatedAt`.

**Events**: Domain entities dispatch events through `DomainEventDispatcherInterface` (currently in-memory with optional logging wrapper). Listeners subscribe via config.

**Session state**: Public users stored in session as serialized object; admin users separate cookie. Retrieved via `PublicSessionInterface` / `AdminSessionInterface`.

## Localization & RBAC

**Translation**: JSON files in `translations/{locale}.json`. Loaded via Symfony Translator with locale prefix in route (enforced by middleware).

**RBAC**: Roles/permissions defined in `settings.php` under `rbac.roles` (map of role → parent roles & permissions). Enforced via:
- `Policy` service (Laminas RBAC instance)
- Template helpers: `@can('permission')` in Plates
- Middleware: `AdminAuthenticationMiddleware` guards admin routes

**Template extensions**: Custom `RbacExtension` provides `@can()`, `@role()` helpers to Plates templates.

## Frontend Integration

**Assets**: Vite MPA in `frontend/` bundles JS/CSS/React. Dev server proxied via `ASSET_DEV_SERVER` env var.

**React mounts**: PHP templates instantiate React components via `@react()` helper (wired to custom `ReactExtension`). Maps mount IDs to entry points in `frontend/src/`.

**Build output**: `npm run build` → `public/assets/` with manifest for template resolution.

## Code Conventions

1. **Strict types**: All PHP files declare `strict_types=1`
2. **Final classes**: Use `final readonly class` for value objects; `final class` for services
3. **Type hints**: Complete parameter + return types; use `?Type` for nullables
4. **Interfaces**: Domain uses interfaces (e.g., `UserRepositoryInterface`); integration provides implementations
5. **Dependency injection**: Constructor injection only; type-hinted parameters resolved by container
6. **Namespacing**: `App\Domain\*`, `App\Feature\*`, `App\Integration\*`, `App\Web\*`
7. **Naming**: Controllers suffixed with `Controller`; services with `Service` or verb-noun (e.g., `AdminAuthenticator`)
8. **Testing**: `Tests\Unit\*`, `Tests\Functional\*`, `Tests\Integration\*` mirror `src/` structure

## Common Tasks

**Add new route**:
1. Create controller in `src/Web/{Area}/Controller/`
2. Register in `config/routes.php` with group/middleware
3. Inject dependencies (e.g., `TemplateRenderer`, repositories)
4. Return response via `$templates->render()`

**Add new entity**:
1. Create in `src/Domain/{Entity}/` with Doctrine attributes
2. Create `{Entity}RepositoryInterface` in same directory
3. Implement `Doctrine\{Entity}Repository` in `src/Integration/Repository/Doctrine/`
4. Wire in `config/container.php`

**Add permission/role**:
1. Update `rbac.roles` in `config/settings.php`
2. Use `@can('permission')` in templates or `$policy->isGranted()` in code

**Template rendering**:
- Call `$this->templates->render($response, 'folder::file', $data)`
- Plates looks in `templates/{folder}/file.php`
- Data available as `$var` in template scope

## External Dependencies

- **Slim 4**: HTTP routing, middleware, PSR-7
- **PHP-DI**: Dependency injection container
- **Doctrine ORM 2.15**: Entity mapping, persistence, migrations
- **Monolog 3.3**: Logging with file rotation
- **Symfony components**: Forms, Validation, CSRF, Translation, Console
- **Laminas RBAC**: Role-based authorization
- **Plates 3.6**: Fast PHP templating (custom lightweight renderer)
- **Odan\Session**: Database session storage
- **Vite**: Frontend bundling (vanilla JS + React)
- **React 18**: Optional interactive components in templates

## Debugging & Logs

**Logs**: `logs/` directory; daily rotated files named `app-YYYY-MM-DD.log`.

**Config inspection**: Call `$container->get('settings')` to dump full merged config.

**Database**: Doctrine queries logged via Doctrine listener if `DOCTRINE_QUERY_LOGS=1` in `.env`.

**Session debug**: Check `PHPSESSID` (public) vs `ADMINPHPSESSID` (admin) cookies in browser DevTools.
