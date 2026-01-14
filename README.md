# Slim Market Demo

This project is a Slim 4 based authentication and RBAC demo application. It
ships with preconfigured public and admin home pages rendered via
Plates-compatible PHP templates so you can explore the layout and access
management flows without wiring up a full back end first.

The lightweight renderer that powers these templates lives in
`src/Infrastructure/View/Plates` and offers a Plates-style API without the Twig
dependency previously used by the project.

## Project entry point

The HTTP front controller lives at `public/index.php` and simply boots the Slim
application by requiring `config/bootstrap.php`.

## Prerequisites

- PHP 8.1 or newer with the `pdo` extension enabled
- [Composer](https://getcomposer.org/) for dependency management

## Installation

Install the PHP dependencies with Composer:

```bash
composer install
```

Copy the default environment configuration if you need local overrides:

```bash
cp env.example .env
```

## Running the demo locally

Start the PHP development server from the project root and point it at the
`public/` directory:

```bash
php -S 0.0.0.0:8080 -t public public/index.php
```

You can then open <http://localhost:8080> in your browser for the public home
page, or <http://localhost:8080/admin> for the admin dashboard.

## Running the admin Vite dev server

The admin dashboard styles/scripts are bundled separately from the React
workspace. To serve them in development, start the dedicated Vite server:

```bash
npm run dev:admin
```

Make sure your `.env` file points `ADMIN_DEV_SERVER` at the same host/port
(the default configuration uses <http://localhost:5175>). Keep this distinct
from the React dev server (for `npm run dev`) so the admin entry point resolves
correctly.

## Building the React bundle

The repository still ships with the Vite-powered React scaffolding used to
preview the role and permission data rendered in the RBAC templates. If you
extend the demo with your own React mounts, generate the production assets by
running:

```bash
npm run build
```

The first run automatically installs the React dependencies into the
`frontend/` workspace before executing the Vite build. If you prefer to manage
the installation yourself you can run `npm run frontend:install` once and
subsequent `npm --prefix frontend run build` commands will skip the automatic
step.

The build output is written to `public/assets/react/` by default and is
automatically picked up by the PHP templates when present.

You can customise the output directory and the public URL that Slim uses to
serve the bundles by setting the following environment variables in your root
`.env` file before building:

```dotenv
REACT_ASSET_BUILD_PATH="/absolute/or/project-relative/path/to/output"
REACT_ASSET_PUBLIC_PREFIX="/custom/public/prefix/"
```

Relative paths are resolved from the project root, matching the PHP
configuration. If the resolved path points inside `frontend/public`, the Vite
configuration skips copying the static assets from that directory to avoid
recursive nesting of generated folders.

## Running tests

Once dependencies are installed you can execute the PHPUnit test suite:

```bash
composer test
```

## Checking permissions in Plates templates

Templates rendered through the custom Plates integration expose a `can`
function for RBAC checks. It resolves the caller’s roles and defers to the
shared `Policy` service so authorization is consistent across the app. The
helper is registered by `App\Integration\View\Plates\RbacExtension` and
supports an optional subject argument when you need to evaluate another user or
a specific role set.

```php
<?php if ($this->can('admin.access')): ?>
    <!-- Render admin navigation when the current user has the ability -->
<?php endif; ?>

<?php if ($this->can('admin.users.manage', $user ?? null)): ?>
    <!-- Check against an explicit user/role payload rather than the session -->
<?php endif; ?>

<?php if ($this->can('admin.users.view')): ?>
    <!-- Hide or show a users listing card in the admin area -->
    <?= $this->insert('admin::users/card') ?>
<?php else: ?>
    <p class="text-muted">You do not have permission to view users.</p>
<?php endif; ?>
```

Under the hood the helper:

- Pulls the current user array from the session when no subject is provided.
- Accepts `App\Domain\Auth\Identity` instances, associative arrays with a
  `roles` key, plain role lists, or stringable values and normalizes them into a
  role array.
- Lowercases and trims the requested ability before delegating to the RBAC
  policy’s `isGranted` check, so `can('Admin.Access')` matches the `admin.access`
  rule.

Providing an invalid ability or role returns `false`, allowing templates to
fail closed without throwing. This mirrors the behavior used by the API layer’s
authorization middleware, so a `can` check in Plates reflects the same decision
as a server-side guard.
