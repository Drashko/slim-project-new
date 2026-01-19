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
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$adminRootPath = parse_url($this->locale_url('admin', null, 'admin'), PHP_URL_PATH) ?? '';
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">

    <!-- Admin assets (Vite) -->
    <?= $this->vite_assets('admin') ?>
    <?= $this->section('styles') ?>
    <style>
        .admin-layout {
            --admin-bg: #f6f8fc;
            --admin-surface: #ffffff;
            --admin-border: #dadce0;
            --admin-border-strong: #c2c7ce;
            --admin-text: #202124;
            --admin-muted: #5f6368;
            --admin-muted-strong: #3c4043;
            --admin-primary: #1a73e8;
            --admin-primary-hover: #1967d2;
            --admin-primary-soft: #e8f0fe;
            --admin-focus: rgba(26, 115, 232, 0.32);
            --admin-shadow-sm: 0 1px 2px rgba(60, 64, 67, 0.12);
            --admin-shadow-md: 0 2px 6px rgba(60, 64, 67, 0.16);
            --admin-radius: 3px;
            background: var(--admin-bg);
            color: var(--admin-text);
            font-family: "Roboto", "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        .admin-layout .admin-shell {
            min-height: 100vh;
            background: var(--admin-bg);
        }

        .admin-layout .admin-sidebar {
            width: 260px;
            background: var(--admin-surface);
            color: var(--admin-muted);
            border-right: 1px solid var(--admin-border);
            box-shadow: none;
        }

        .admin-layout .admin-sidebar .navbar-brand {
            letter-spacing: 0.03em;
            color: var(--admin-text);
        }

        .admin-layout .admin-sidebar .navbar-collapse {
            padding-top: 0.5rem;
        }

        .admin-layout .admin-sidebar .nav-link {
            color: var(--admin-muted);
            border-radius: var(--admin-radius);
            padding: 0.55rem 0.95rem;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .admin-layout .admin-sidebar .nav-link:hover,
        .admin-layout .admin-sidebar .nav-link:focus,
        .admin-layout .admin-sidebar .nav-link.active {
            color: var(--admin-primary);
            background: var(--admin-primary-soft);
            border-radius: var(--admin-radius);
        }

        .admin-layout .admin-sidebar .dropdown-menu {
            border-radius: var(--admin-radius);
            border: 1px solid var(--admin-border);
            background: var(--admin-surface);
            box-shadow: var(--admin-shadow-md);
        }

        .admin-layout .admin-sidebar .dropdown-item {
            border-radius: var(--admin-radius);
            color: var(--admin-muted);
            font-weight: 500;
        }

        .admin-layout .admin-sidebar .dropdown-item:focus,
        .admin-layout .admin-sidebar .dropdown-item:hover,
        .admin-layout .admin-sidebar .dropdown-item.active {
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
        }

        .admin-layout .admin-main {
            padding: 1.5rem 1.75rem 2.5rem;
        }

        .admin-layout main .container-fluid {
            max-width: none;
            width: 100%;
        }

        .admin-layout .admin-topbar {
            background: var(--admin-surface);
            border-radius: var(--admin-radius);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-shadow-sm);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .admin-layout .admin-topbar .admin-title {
            font-weight: 500;
            color: var(--admin-text);
        }

        .admin-layout .admin-topbar .admin-actions {
            gap: 0.75rem;
        }

        .admin-layout .admin-topbar .form-select {
            border-radius: var(--admin-radius);
            border-color: var(--admin-border);
        }

        .admin-layout .admin-content-surface {
            background: var(--admin-surface);
            border-radius: var(--admin-radius);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-shadow-sm);
        }

        .admin-layout .content-header .text-uppercase {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.7rem;
            border-radius: var(--admin-radius);
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            font-weight: 500;
            letter-spacing: 0.08em;
        }

        .admin-layout .content-header h1,
        .admin-layout .content-header .h3 {
            color: var(--admin-text);
            font-weight: 500;
        }

        .admin-layout .content-header p.text-secondary {
            color: var(--admin-muted) !important;
        }

        .admin-layout .card {
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow-sm);
        }

        .admin-layout .card.card-outline {
            border: 1px solid var(--admin-border);
        }

        .admin-layout .card-header {
            background: #fafafa;
            border-bottom: 1px solid var(--admin-border);
            border-top-left-radius: var(--admin-radius);
            border-top-right-radius: var(--admin-radius);
        }

        .admin-layout .card-title {
            font-weight: 500;
            color: var(--admin-text);
        }

        .admin-layout .form-label {
            color: var(--admin-muted);
            font-weight: 500;
        }

        .admin-layout .form-control,
        .admin-layout .form-select {
            border-radius: var(--admin-radius);
            border-color: var(--admin-border);
            box-shadow: none;
            color: var(--admin-text);
        }

        .admin-layout .form-control::placeholder {
            color: #9aa0a6;
        }

        .admin-layout .form-control:focus,
        .admin-layout .form-select:focus {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 0.2rem var(--admin-focus);
        }

        .admin-layout .form-control:disabled,
        .admin-layout .form-select:disabled {
            background: #f1f3f4;
            color: #9aa0a6;
        }

        .admin-layout .form-check-input {
            border-color: var(--admin-border);
        }

        .admin-layout .form-check-input:checked {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
        }

        .admin-layout .btn-primary {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
            box-shadow: 0 1px 2px rgba(26, 115, 232, 0.3);
        }

        .admin-layout .btn-primary:hover,
        .admin-layout .btn-primary:focus {
            background: var(--admin-primary-hover);
            border-color: var(--admin-primary-hover);
        }

        .admin-layout .btn-outline-secondary {
            color: var(--admin-muted);
            border-color: var(--admin-border);
        }

        .admin-layout .btn-outline-secondary:hover,
        .admin-layout .btn-outline-secondary:focus {
            color: var(--admin-primary);
            border-color: var(--admin-primary);
            background: var(--admin-primary-soft);
        }

        .admin-layout .btn:focus-visible,
        .admin-layout .nav-link:focus-visible,
        .admin-layout .form-control:focus-visible,
        .admin-layout .form-select:focus-visible,
        .admin-layout .dropdown-item:focus-visible {
            outline: 2px solid var(--admin-primary);
            outline-offset: 2px;
            box-shadow: none;
        }

        .admin-layout .table > :not(caption) > * > * {
            border-color: var(--admin-border);
        }

        .admin-layout .table thead th {
            color: var(--admin-muted);
            font-weight: 500;
            background: #fafafa;
        }

        .admin-layout .admin-pagination {
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .admin-layout .admin-pagination .page-link {
            border-radius: var(--admin-radius);
            border: 1px solid var(--admin-border);
            color: var(--admin-muted);
            background: var(--admin-surface);
            padding: 0.45rem 0.9rem;
            box-shadow: none;
            transition: all 0.2s ease;
        }

        .admin-layout .admin-pagination .page-link:hover,
        .admin-layout .admin-pagination .page-link:focus {
            color: var(--admin-primary);
            border-color: var(--admin-primary);
            background: var(--admin-primary-soft);
        }

        .admin-layout .admin-pagination .page-item.active .page-link {
            color: #ffffff;
            border-color: transparent;
            background: var(--admin-primary);
            box-shadow: 0 1px 2px rgba(26, 115, 232, 0.3);
        }

        .admin-layout .admin-pagination .page-item.disabled .page-link {
            color: #9aa0a6;
            background: #f1f3f4;
            box-shadow: none;
        }

        @media (min-width: 992px) {
            .admin-layout .admin-sidebar {
                position: sticky;
                top: 0;
                height: 100vh;
                align-self: flex-start;
            }

            .admin-layout .admin-sidebar .navbar-collapse {
                display: flex !important;
                flex-direction: column;
                height: calc(100vh - 72px);
            }
        }

        @media (max-width: 991.98px) {
            .admin-layout .admin-shell {
                flex-direction: column;
            }

            .admin-layout .admin-sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body class="<?= $this->e($bodyClass) ?>">
<div class="admin-shell d-flex">
    <nav class="admin-sidebar navbar navbar-expand-lg navbar-light border-end-0">
        <div class="container-fluid flex-lg-column align-items-stretch p-0">
            <div class="d-flex align-items-center justify-content-between w-100 px-3 py-3 border-bottom">
                <a class="navbar-brand fw-semibold m-0" href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>">
                    <i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i><?= $this->e($this->trans('app.name')) ?> Admin
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse px-3 pb-3" id="adminNav">
                <ul class="navbar-nav flex-column w-100 gap-1">
                    <?php foreach ($primaryLinks as $link): ?>
                        <?php $hasChildren = !empty($link['children']); ?>
                        <?php if ($hasChildren): ?>
                            <?php
                                $childStates = [];
                                $isDropdownActive = false;
                                foreach ($link['children'] as $index => $child) {
                                    $childPath = parse_url($child['href'] ?? '', PHP_URL_PATH) ?? '';
                                    $childActive = $childPath !== '' && ($currentPath === $childPath || str_starts_with($currentPath, rtrim($childPath, '/') . '/'));
                                    $childStates[$index] = $childActive;
                                    $isDropdownActive = $isDropdownActive || $childActive;
                                }
                            ?>
                            <?php $dropdownId = 'adminDropdown_' . md5((string) ($link['label'] ?? 'link')); ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle<?= $isDropdownActive ? ' active' : '' ?>" href="#" id="<?= $this->e($dropdownId) ?>" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                    <?= $this->e($link['label'] ?? '') ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="<?= $this->e($dropdownId) ?>">
                                    <?php foreach ($link['children'] as $index => $child): ?>
                                        <li>
                                            <a class="dropdown-item<?= !empty($childStates[$index]) ? ' active' : '' ?>" href="<?= $this->e($child['href'] ?? '#') ?>">
                                                <?php if (!empty($child['icon'])): ?><i class="<?= $this->e($child['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                                <?= $this->e($child['label'] ?? '') ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <?php $linkPath = parse_url($link['href'] ?? '', PHP_URL_PATH) ?? ''; ?>
                            <?php
                                $isActive = $linkPath !== '' && (
                                    ($linkPath === $adminRootPath && $currentPath === $adminRootPath)
                                    || ($linkPath !== $adminRootPath && ($currentPath === $linkPath || str_starts_with($currentPath, rtrim($linkPath, '/') . '/')))
                                );
                            ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $isActive ? ' active' : '' ?>" href="<?= $this->e($link['href'] ?? '#') ?>">
                                    <?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                    <?= $this->e($link['label'] ?? '') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        <div class="admin-main">
            <header class="admin-topbar d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="admin-title h5 mb-0"><?= $this->e($title) ?></div>
                <div class="admin-actions d-flex flex-wrap align-items-center">
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
                        <div class="dropdown">
                            <button
                                class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                type="button"
                                id="adminUserMenu"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i class="fa-solid fa-user me-1" aria-hidden="true"></i><?= $this->e($user['email'] ?? '') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUserMenu">
                                <li>
                                    <a class="dropdown-item" href="<?= $this->e($this->locale_url('admin/profile', null, 'admin')) ?>">
                                        <i class="fa-solid fa-id-card me-2" aria-hidden="true"></i><?= $this->e($this->trans('layout.nav.profile')) ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= $this->e($this->locale_url('admin/logout', null, 'admin')) ?>">
                                        <i class="fa-solid fa-right-from-bracket me-2" aria-hidden="true"></i><?= $this->e($this->trans('layout.account.sign_out')) ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </header>
            <div class="container-fluid py-4 admin-content-surface">
                <?= $this->section('content') ?>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
