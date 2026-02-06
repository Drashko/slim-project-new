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
php -S 0.0.0.0:8080 -t home home/index.php
```

You can then open <http://localhost:8080> in your browser for the public home
page, or <http://localhost:8080/admin> for the admin dashboard.

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
