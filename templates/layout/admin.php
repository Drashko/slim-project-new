<?php
/** @var array|null $user */
/** @var string|null $title */

$title = $title ?? $this->trans('app.default_title');
$bodyClass = 'admin-layout bg-light';

$primaryLinks = [
    [
        'href' => $this->locale_url('admin', null, 'admin'),
        'label' => $this->trans('layout.nav.dashboard'),
        'icon' => 'fa-solid fa-gauge',
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
    <nav class="admin-sidebar navbar navbar-expand-lg navbar-light border-end-0 align-self-stretch sticky-top">
        <div class="container-fluid flex-lg-column align-items-stretch p-0">
            <div class="d-flex align-items-center justify-content-between w-100 px-3 py-3 border-bottom">
                <a class="navbar-brand fw-semibold m-0" href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>">
                    <i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i><?= $this->e($this->trans('app.name')) ?> <?= $this->e($this->trans('admin.badge')) ?>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="<?= $this->e($this->trans('layout.nav.toggle')) ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse px-3 pb-3 mt-2" id="adminNav">
                <ul class="navbar-nav flex-column w-100 gap-1">
                    <?php foreach ($primaryLinks as $link): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->e($link['href'] ?? '#') ?>">
                                <?php if (!empty($link['icon'])): ?><i class="<?= $this->e($link['icon']) ?> me-1" aria-hidden="true"></i><?php endif; ?>
                                <?= $this->e($link['label'] ?? '') ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        <div class="admin-main">
            <div class="border-bottom bg-white">
                <div class="container-fluid py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-2"></div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="adminLanguageSwitch" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-globe me-1" aria-hidden="true"></i><?= $this->e($this->trans('layout.language.switch')) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminLanguageSwitch">
                            <?php foreach ($this->available_locales() as $locale => $label): ?>
                                <li>
                                    <a class="dropdown-item<?= $this->current_locale() === $locale ? ' active' : '' ?>" href="<?= $this->e($this->locale_switch_url($locale)) ?>">
                                        <?= $this->e($label) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
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
