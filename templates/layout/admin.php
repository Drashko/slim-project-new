<?php
/** @var array|null $user */
/** @var string|null $title */

$user = is_array($user ?? null) ? $user : null;
$title = $title ?? $this->trans('app.default_title');
$canAccessAdminArea = $this->can('admin.access', $user ?? null);
$canManageUsers = $this->can('admin.users.manage', $user ?? null);
$isAdminAuthenticated = $canAccessAdminArea && $user !== null && isset($user['email']);
$availableLocales = $this->available_locales();
$currentLocale = $this->current_locale();
if (!is_string($currentLocale) || $currentLocale === '') {
    $currentLocale = (string) array_key_first($availableLocales);
}
if ($currentLocale === '') {
    $currentLocale = 'en';
}

$bodyClass = 'admin-layout bg-light';

$primaryLinks = $isAdminAuthenticated
    ? array_values(array_filter([
        [
            'href' => $this->locale_url('admin', null, 'admin'),
            'label' => $this->trans('layout.nav.dashboard'),
            'icon' => 'fa-solid fa-gauge',
        ],
        $canManageUsers ? [
            'href' => $this->locale_url('admin/users', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_users'),
            'icon' => 'fa-solid fa-users-gear',
            'children' => [
                [
                    'href' => $this->locale_url('admin/users', null, 'admin'),
                    'label' => $this->trans('layout.nav.admin_users_all'),
                    'icon' => 'fa-solid fa-list',
                ],
                [
                    'href' => $this->locale_url('admin/users/new', null, 'admin'),
                    'label' => $this->trans('layout.nav.admin_users_add'),
                    'icon' => 'fa-solid fa-user-plus',
                ],
            ],
        ] : null,
        $canManageUsers ? [
            'href' => $this->locale_url('admin/roles', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_roles'),
            'icon' => 'fa-solid fa-layer-group',
        ] : null,
        $canManageUsers ? [
            'href' => $this->locale_url('admin/permissions', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_permissions'),
            'icon' => 'fa-solid fa-user-shield',
        ] : null,
        [
            'href' => $this->locale_url('admin/ads', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_ads'),
            'icon' => 'fa-solid fa-bullhorn',
        ],
        [
            'href' => $this->locale_url('admin/categories', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_categories'),
            'icon' => 'fa-solid fa-tags',
        ],
        [
            'href' => $this->locale_url('admin/audit', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_audit'),
            'icon' => 'fa-solid fa-clipboard-list',
        ],
    ], static fn(?array $link): bool => $link !== null))
    : [
        [
            'href' => $this->locale_url('admin/login', null, 'admin'),
            'label' => $this->trans('layout.nav.admin_login'),
            'icon' => 'fa-solid fa-right-to-bracket',
        ],
        [
            'href' => $this->locale_url('auth/login', null, 'public'),
            'label' => $this->trans('layout.nav.profile_login'),
            'icon' => 'fa-solid fa-user-lock',
        ],
    ];
?>
<!DOCTYPE html>
<html lang="<?= $this->e($this->current_locale() ?? 'en') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Admin assets (Vite) -->
    <?= $this->vite_assets('admin') ?>
    <?= $this->section('styles') ?>
</head>
<body class="<?= $this->e($bodyClass) ?>">
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold text-primary" href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>">
            <i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i><?= $this->e($this->trans('app.name')) ?> Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($primaryLinks as $link): ?>
                    <?php $hasChildren = !empty($link['children']); ?>
                    <?php if ($hasChildren): ?>
                        <?php $dropdownId = 'adminDropdown_' . md5((string) ($link['label'] ?? 'link')); ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="<?= $this->e($dropdownId) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                <?= $this->e($link['label'] ?? '') ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="<?= $this->e($dropdownId) ?>">
                                <?php foreach ($link['children'] as $child): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= $this->e($child['href'] ?? '#') ?>">
                                            <?php if (!empty($child['icon'])): ?><i class="<?= $this->e($child['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                            <?= $this->e($child['label'] ?? '') ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->e($link['href'] ?? '#') ?>">
                                <?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                <?= $this->e($link['label'] ?? '') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <select
                    class="form-select form-select-sm"
                    aria-label="<?= $this->e($this->trans('layout.language.switch')) ?>"
                    onchange="if (this.value) { window.location.href = this.value; }"
                >
                    <?php foreach ($availableLocales as $locale => $label): ?>
                        <option
                            value="<?= $this->e($this->locale_switch_url($locale)) ?>"
                            <?= $currentLocale === $locale ? 'selected' : '' ?>
                        >
                            <?= $this->e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isAdminAuthenticated): ?>
                    <span class="text-muted small"><?= $this->e($user['email'] ?? '') ?></span>
                    <a class="btn btn-outline-danger btn-sm" href="<?= $this->e($this->locale_url('admin/logout', null, 'admin')) ?>">
                        <?= $this->e($this->trans('layout.account.sign_out')) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <?= $this->section('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
