<?php
/** @var array|null $user */
/** @var string|null $title */

$user = is_array($user ?? null) ? $user : null;
$title = $title ?? $this->trans('app.default_title');
$bodyClass = 'public-layout light-theme';
$primaryLinks = [
    ['href' => $this->locale_url(null, null, 'public'), 'label' => $this->trans('layout.nav.public_home')],
    ['href' => $this->locale_url('auth/login', null, 'public'), 'label' => $this->trans('layout.nav.profile_login')],
    ['href' => $this->locale_url('auth/register', null, 'public'), 'label' => $this->trans('layout.nav.register')],
];
?>
<!DOCTYPE html>
<html lang="<?= $this->e($this->current_locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="api-base" content="<?= $this->e($_ENV['API_BASE'] ?? '/') ?>">
    <title><?= $this->e($title) ?></title>

    <link rel="stylesheet" href="/digiboard/assets/vendor/css/all.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/jquery-ui.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/daterangepicker.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/select2.min.css">
    <link rel="stylesheet" href="/digiboard/assets/vendor/css/bootstrap.min.css">
    <link rel="stylesheet" href="/digiboard/assets/css/style.css">
    <link rel="stylesheet" id="primaryColor" href="/digiboard/assets/css/blue-color.css">
    <link rel="stylesheet" href="/assets/front.css">
    <?= $this->section('head') ?>
</head>
<body class="<?= $this->e($bodyClass) ?>">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold text-primary" href="<?= $this->e($this->locale_url()) ?>">
                <i class="fa-solid fa-bag-shopping me-2" aria-hidden="true"></i><?= $this->e($this->trans('app.name')) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-2">
                    <?php foreach ($primaryLinks as $link): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $this->e($link['href']) ?>"><?= $this->e($link['label']) ?></a></li>
                    <?php endforeach; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="languageSwitch" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-globe me-1" aria-hidden="true"></i><?= $this->e($this->trans('layout.language.switch')) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageSwitch">
                            <?php foreach ($this->available_locales() as $locale => $label): ?>
                                <li>
                                    <a class="dropdown-item<?= $this->current_locale() === $locale ? ' active' : '' ?>" href="<?= $this->e($this->locale_switch_url($locale)) ?>">
                                        <?= $this->e($label) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if ($user !== null && isset($user['email'])): ?>
                        <li class="nav-item d-flex flex-column flex-lg-row align-items-lg-center gap-lg-2">
                            <span class="navbar-text small text-secondary"><?= $this->trans('layout.account.signed_in_as', ['%email%' => $user['email'] ?? '']) ?></span>
                            <a class="nav-link" href="<?= $this->e($this->locale_url('profile')) ?>">
                                <i class="fa-solid fa-user me-1" aria-hidden="true"></i><?= $this->e($this->trans('layout.nav.profile')) ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?= $this->e($this->locale_url('auth/logout')) ?>">
                                <i class="fa-solid fa-right-from-bracket me-1" aria-hidden="true"></i><?= $this->e($this->trans('layout.account.sign_out')) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="content pt-4 pb-5">
            <div class="container">
                <?= $this->section('content') ?>
            </div>
        </div>
    </div>

    <footer class="main-footer border-0 text-sm text-muted text-center">
        <div class="container">
            <?= $this->e($this->trans('layout.footer.demo_note')) ?>
        </div>
    </footer>
</div>

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
