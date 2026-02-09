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

## Casbin authorization flow

API authorization is handled by a Casbin enforcer registered in the container
and applied as middleware to the `/api/v1` route group. The default model lives
in `config/casbin/model.conf` and ships with a simple RBAC + scope matcher that
supports path patterns, HTTP methods, and a scope segment for client or
server-to-server use cases. Policies are persisted in the database via Doctrine
using the `casbin_rule` table.

### Request headers used by the middleware

- `X-Subject`: Primary subject identifier (user id, role, or service id).
- `X-Client-Id`: Fallback subject for server-to-server calls.
- `Authorization: Bearer <subject>`: Optional bearer value used as a subject
  when `X-Subject` and `X-Client-Id` are missing.
- `X-Scope`: Optional scope string (defaults to `api`).

### Example policy entry (database row)

```
p, role_admin, /api/v1/admin, GET, api
g, user:1, role_admin
```

### Example API call

```
curl -H "X-Subject: user:1" -H "X-Scope: api" http://localhost:8080/api/v1/admin
```

Policies are stored in the `casbin_rule` table via a Doctrine-backed Casbin
adapter configured in `config/container.php`, sharing the same Doctrine
connection settings defined in `config/settings.php`.
