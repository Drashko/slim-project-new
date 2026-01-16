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
    <style>
        .admin-layout {
            background: #f4f6fb;
        }

        .admin-layout .admin-shell {
            min-height: 100vh;
            background: #f4f6fb;
        }

        .admin-layout .admin-sidebar {
            width: 260px;
            background: #ffffff;
            color: #475569;
            box-shadow: 0 20px 40px rgba(148, 163, 184, 0.2);
        }

        .admin-layout .admin-sidebar .navbar-brand {
            letter-spacing: 0.03em;
            color: #0f172a;
        }

        .admin-layout .admin-sidebar .nav-link {
            color: #475569;
            border-radius: 12px;
            padding: 0.6rem 0.85rem;
            transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .admin-layout .admin-sidebar .nav-link:hover,
        .admin-layout .admin-sidebar .nav-link:focus,
        .admin-layout .admin-sidebar .nav-link.active {
            color: #1d4ed8;
            background: rgba(59, 130, 246, 0.12);
            transform: translateX(2px);
        }

        .admin-layout .admin-sidebar .dropdown-menu {
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: #ffffff;
            box-shadow: 0 20px 30px rgba(148, 163, 184, 0.25);
        }

        .admin-layout .admin-sidebar .dropdown-item {
            border-radius: 10px;
            color: #475569;
        }

        .admin-layout .admin-sidebar .dropdown-item:focus,
        .admin-layout .admin-sidebar .dropdown-item:hover {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .admin-layout .admin-main {
            padding: 1.5rem 1.75rem 2.5rem;
        }

        .admin-layout main .container-fluid {
            max-width: 1220px;
        }

        .admin-layout .admin-topbar {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .admin-layout .admin-topbar .admin-title {
            font-weight: 600;
            color: #111827;
        }

        .admin-layout .admin-topbar .admin-actions {
            gap: 0.75rem;
        }

        .admin-layout .admin-topbar .form-select {
            border-radius: 12px;
            border-color: #e2e8f0;
        }

        .admin-layout .admin-content-surface {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
        }

        .admin-layout .admin-pagination {
            gap: 0.45rem;
        }

        .admin-layout .admin-pagination .page-link {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            color: #334155;
            background: #ffffff;
            padding: 0.45rem 0.9rem;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
            transition: all 0.2s ease;
        }

        .admin-layout .admin-pagination .page-link:hover,
        .admin-layout .admin-pagination .page-link:focus {
            color: #1d4ed8;
            border-color: rgba(59, 130, 246, 0.4);
            box-shadow: 0 16px 26px rgba(59, 130, 246, 0.18);
        }

        .admin-layout .admin-pagination .page-item.active .page-link {
            color: #ffffff;
            border-color: transparent;
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            box-shadow: 0 16px 28px rgba(37, 99, 235, 0.35);
        }

        .admin-layout .admin-pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background: #f1f5f9;
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
                        <span class="text-muted small"><?= $this->e($user['email'] ?? '') ?></span>
                        <a class="btn btn-outline-danger btn-sm" href="<?= $this->e($this->locale_url('admin/logout', null, 'admin')) ?>">
                            <?= $this->e($this->trans('layout.account.sign_out')) ?>
                        </a>
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
