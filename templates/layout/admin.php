<?php
/** @var array|null $user */
/** @var string|null $title */

$user = is_array($user ?? null) ? $user : null;
$title = $title ?? $this->trans('app.default_title');
$canAccessAdminArea = $this->can('admin.access', $user ?? null);
$canManageUsers = $this->can('admin.users.manage', $user ?? null);
$isAdminAuthenticated = $canAccessAdminArea && $user !== null && isset($user['email']);
$adminNavMode = 'top';
$availableLocales = $this->available_locales();
$currentLocale = $this->current_locale();
if (!is_string($currentLocale) || $currentLocale === '') {
    $currentLocale = (string) array_key_first($availableLocales);
}
if ($currentLocale === '') {
    $currentLocale = 'en';
}

if ($isAdminAuthenticated) {
    $cookieNavMode = $_COOKIE['admin_nav_mode'] ?? 'top';
    $adminNavMode = in_array($cookieNavMode, ['top', 'aside'], true) ? $cookieNavMode : 'top';
}

// Match Digiboard demo layout classes for 1:1 spacing/behavior.
// body-padding + body-p-top are required for correct sidebar/content sizing.
$bodyClass = $isAdminAuthenticated
    ? 'body-padding body-p-top light-theme'
    : 'light-theme';

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
<html lang="<?= $this->e($this->current_locale() ?? 'en') ?>" data-menu="vertical" data-nav-size="nav-default">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?></title>

    <!-- Keep Digiboard vendor stack aligned with the demo to avoid spacing/color drift -->
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/all.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/jquery-ui.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/daterangepicker.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/select2.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/bootstrap.min.css">
    <link rel="stylesheet" href="/digiboard/assets/css/style.css">
    <link rel="stylesheet" id="primaryColor" href="/digiboard/assets/css/blue-color.css">
    <?= $this->section('styles') ?>
</head>
<body class="<?= $this->e($bodyClass) ?>">

<?php if ($isAdminAuthenticated): ?>
    <!-- header start (Digiboard structure) -->
    <div class="header">
        <div class="row g-0 align-items-center">
            <div class="col-xxl-6 col-xl-5 col-4 d-flex align-items-center gap-20">
                <div class="main-logo d-lg-block d-none">
                    <div class="logo-big">
                        <a href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>">
                            <img src="/digiboard/assets/images/logo-black.png" alt="Logo">
                        </a>
                    </div>
                    <div class="logo-small">
                        <a href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>">
                            <img src="/digiboard/assets/images/logo-small.png" alt="Logo">
                        </a>
                    </div>
                </div>
                <div class="nav-close-btn">
                    <button id="navClose" type="button"><i class="fa-light fa-bars-sort"></i></button>
                </div>
            </div>
            <div class="col-4 d-lg-none">
                <div class="mobile-logo">
                    <a href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>">
                        <img src="/digiboard/assets/images/logo-black.png" alt="Logo">
                    </a>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-7 col-lg-8 col-4">
                <div class="header-right-btns d-flex justify-content-end align-items-center">
                    <div class="me-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-globe text-muted" aria-hidden="true"></i>
                        <label class="visually-hidden" for="admin-language-switch"><?= $this->e($this->trans('layout.language.switch')) ?></label>
                        <select
                            id="admin-language-switch"
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
                    </div>
                    <div class="dropdown profile-dropdown">
                        <button class="header-btn" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-light fa-user"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <div class="dropdown-header">
                                <h5 class="mb-0"><?= $this->e($user['email'] ?? 'User') ?></h5>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= $this->e($this->locale_url('admin/profile', null, 'admin')) ?>"><i class="fa-light fa-user me-2"></i>Profile</a>
                            <a class="dropdown-item" href="<?= $this->e($this->locale_url('admin/logout', null, 'admin')) ?>"><i class="fa-light fa-arrow-right-from-bracket me-2"></i>Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- header end -->

    <!-- main sidebar start -->
    <div class="main-sidebar">
        <div class="main-menu">
            <ul class="sidebar-menu scrollable">
                <li class="sidebar-item">
                    <a role="button" class="sidebar-link-group-title has-sub">Project</a>
                    <ul class="sidebar-link-group">
                        <?php foreach ($primaryLinks as $link): ?>
                            <?php $hasChildren = !empty($link['children']); ?>
                            <?php $menuId = 'dd_' . md5((string)($link['label'] ?? 'link')); ?>

                            <?php if ($hasChildren): ?>
                                <li class="sidebar-dropdown-item">
                                    <a role="button" class="sidebar-link has-sub" data-dropdown="<?= $this->e($menuId) ?>">
                                        <span class="nav-icon"><?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?>"></i><?php endif; ?></span>
                                        <span class="sidebar-txt"><?= $this->e($link['label'] ?? '') ?></span>
                                    </a>
                                    <ul class="sidebar-dropdown-menu" id="<?= $this->e($menuId) ?>">
                                        <?php foreach ($link['children'] as $child): ?>
                                            <li class="sidebar-dropdown-item">
                                                <a class="sidebar-link" href="<?= $this->e($child['href'] ?? '#') ?>"><?= $this->e($child['label'] ?? '') ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="sidebar-dropdown-item">
                                    <a href="<?= $this->e($link['href'] ?? '#') ?>" class="sidebar-link">
                                        <span class="nav-icon"><?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?>"></i><?php endif; ?></span>
                                        <span class="sidebar-txt"><?= $this->e($link['label'] ?? '') ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- main sidebar end -->

    <!-- main content start -->
    <div class="main-content">
        <div class="dashboard-breadcrumb mb-25">
            <h2><?= $this->e($page_title ?? $title) ?></h2>
            <div>
                <?= $this->section('breadcrumb_right') ?>
            </div>
        </div>

        <?= $this->section('content') ?>
    </div>
    <!-- main content end -->

<?php else: ?>
    <!-- unauthenticated admin pages (e.g. login) -->
    <div class="main-content">
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3">
            <i class="fa-solid fa-globe text-muted" aria-hidden="true"></i>
            <label class="visually-hidden" for="admin-language-switch-guest"><?= $this->e($this->trans('layout.language.switch')) ?></label>
            <select
                id="admin-language-switch-guest"
                class="form-select form-select-sm w-auto"
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
        </div>
        <?= $this->section('content') ?>
    </div>
<?php endif; ?>

<script src="/digiboard/assets/vendor/js/jquery-3.6.0.min.js"></script>
<script src="/digiboard/assets/vendor/js/jquery-ui.min.js"></script>
<script src="/digiboard/assets/vendor/js/OverlayScrollbars.min.js"></script>
<script src="/digiboard/assets/vendor/js/moment.min.js"></script>
<script src="/digiboard/assets/vendor/js/daterangepicker.js"></script>
<script src="/digiboard/assets/vendor/js/select2.min.js"></script>
<script src="/digiboard/assets/vendor/js/bootstrap.bundle.min.js"></script>
<script src="/digiboard/assets/js/main.js"></script>

<?= $this->section('scripts') ?>
</body>
</html>
