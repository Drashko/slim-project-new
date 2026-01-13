# Template Localization and RBAC Helpers

This document explains the custom helper functions registered in the Plates view engine for language selection, template rendering conveniences, and RBAC checks.

## Localization helpers (languages)

The container configures a `Translator` and `PathLocalizer` from `config/container.php`, then registers several helper functions on the Plates engine:

- `trans(id, parameters = [], domain = null, locale = null)`: Proxies to the Symfony translator with the configured JSON resources so templates can render translated strings.
- `current_locale()`: Returns the translator’s active locale, reflecting the `LocalizationMiddleware`-managed value or default locale.
- `available_locales()`: Exposes the configured locale map from `settings['localization']['supported_locales']` for building language switchers.
- `locale_name(locale)`: Resolves the human-friendly label for a locale from the same map, falling back to the raw code.
- `locale_url(path = null, locale = null, scope = null)`: Generates a localized URL by picking the requested locale, an admin/public scoped locale stored in session, or the translator’s current locale. It prefixes the path using `PathLocalizer` so canonical routes map to their localized slugs.
- `locale_switch_url(locale)`: Rewrites the current request URI to the requested locale, removing any `lang` query param and canonicalizing the path via `PathLocalizer` so translated and canonical routes resolve consistently.

These helpers allow templates to present locale-aware navigation without duplicating routing logic.

### Localization usage examples

Render a translated string with variables and fall back to the current locale:

```php
<h1><?= $this->trans('welcome.message', ['%name%' => $user->getName()]) ?></h1>
```

Build a language switcher that links back to the current page with the requested locale applied:

```php
<ul class="locale-switcher">
    <?php foreach ($this->available_locales() as $code => $label): ?>
        <li class="<?= $code === $this->current_locale() ? 'active' : '' ?>">
            <a href="<?= $this->locale_switch_url($code) ?>">
                <?= $this->locale_name($code) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
```

Link to a translated route (e.g., `/fr/profil`) while respecting admin/public locale scopes:

```php
<a href="<?= $this->locale_url('profile', scope: 'public') ?>">
    <?= $this->trans('layout.nav.profile') ?>
</a>
```

## Template configuration helpers

Beyond localization, the container seeds Plates with convenience helpers:

- Template folders (`layout`, `front`, `auth`, `admin`, `profile`) are registered so templates can reference namespaced paths like `admin::dashboard` without repeating directories.
- Flash messages are injected globally as `flash`, sourced from the started PHP session.
- React asset helper `react_assets()` returns the correct JS and CSS includes based on whether the Vite dev server is active or a manifest is present on disk. It normalizes public prefixes so assets load correctly from either source.

### Template helper usage examples

Render a flash message banner if one exists:

```php
<?php if ($flash->hasMessage('success')): ?>
    <div class="alert alert-success">
        <?= implode('<br>', $flash->getMessage('success')) ?>
    </div>
<?php endif; ?>
```

Include React assets in a layout without hardcoding dev/production URLs:

```php
<?php $assets = $this->react_assets(); ?>
<?php if ($assets['available']): ?>
    <?php foreach ($assets['styles'] as $href): ?>
        <link rel="stylesheet" href="<?= $href ?>">
    <?php endforeach; ?>
    <?php foreach ($assets['scripts'] as $src): ?>
        <script type="module" src="<?= $src ?>"></script>
    <?php endforeach; ?>
<?php else: ?>
    <!-- React assets missing; consider showing a warning in dev -->
<?php endif; ?>
```

Reference a namespaced template folder to avoid repeating directories:

```php
<?= $this->insert('admin::partials/sidebar', ['active' => 'dashboard']) ?>
```

## RBAC helpers

The custom `App\Integration\View\Plates\RbacExtension` attaches RBAC-aware helpers that delegate to the shared `Policy` service:

- `can(ability, subject = null)`: Returns `true` when the given ability (e.g., `admin.access`) is granted to the resolved roles. If no subject is supplied, it pulls the current session user and uses their roles.
- `cannot(ability, subject = null)`: Negation of `can`, useful for concise template conditions.
- `current_user_roles()`: Returns the normalized role list for the current session user, aiding UI decisions that depend on role names.

The extension normalizes role input from `Identity` objects, arrays with a `roles` key, raw role lists, or stringable values, so permission checks stay consistent across templates and the API layer.

### RBAC usage examples

Show an admin-only navigation link:

```php
<?php if ($this->can('admin.access')): ?>
    <a href="<?= $this->locale_url('admin/dashboard', scope: 'admin') ?>">
        <?= $this->trans('nav.admin_dashboard') ?>
    </a>
<?php endif; ?>
```

Guard a button with a negated permission:

```php
<?php if ($this->cannot('admin.users.manage', $user)): ?>
    <button class="btn btn-secondary" disabled aria-disabled="true">
        <?= $this->trans('admin.dashboard.permissions.read_only') ?>
    </button>
<?php endif; ?>
```

Display the current user roles for debugging or auditing banners:

```php
<div class="user-roles">
    <?= implode(', ', $this->current_user_roles()) ?>
</div>
```
