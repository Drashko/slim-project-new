<?php
/** @var array|null $user */
/** @var string|null $title */

$user = is_array($user ?? null) ? $user : null;
$title = $title ?? $this->trans('app.default_title');
$canAccessAdminArea = $this->can('admin.access', $user ?? null);
$canManageUsers = $this->can('admin.users.manage', $user ?? null);
$isAdminAuthenticated = $canAccessAdminArea && $user !== null && isset($user['email']);
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$adminRootPath = parse_url($this->locale_url('admin', null, 'admin'), PHP_URL_PATH) ?? '';

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
<html class="admin-layout" lang="<?= $this->e($this->current_locale() ?? 'en') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($title) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Admin assets (Vite) -->
    <?= $this->vite_assets('admin') ?>
</head>
<body class="<?= $this->e($bodyClass) ?>">
<div class="admin-shell d-flex min-vh-100">
    <?php if ($isAdminAuthenticated): ?>
        <nav class="admin-sidebar navbar navbar-expand-lg navbar-light border-end-0 align-self-start sticky-top">
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
                                    <button class="nav-link dropdown-toggle<?= $isDropdownActive ? ' active' : '' ?>" type="button" id="<?= $this->e($dropdownId) ?>" data-bs-toggle="dropdown" aria-expanded="<?= $isDropdownActive ? 'true' : 'false' ?>">
                                        <?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                        <?= $this->e($link['label'] ?? '') ?>
                                    </button>
                                    <ul class="dropdown-menu<?= $isDropdownActive ? ' show' : '' ?>" aria-labelledby="<?= $this->e($dropdownId) ?>">
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
    <?php endif; ?>

    <main class="flex-grow-1">
        <div class="admin-main">
            <div class="container-fluid py-4 admin-content-surface">
                <?= $this->section('content') ?>
            </div>
            <footer class="admin-footer text-center small">
                &copy; <?= $this->e((new DateTimeImmutable())->format('Y')) ?> <?= $this->e($this->trans('app.name')) ?>.
            </footer>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
