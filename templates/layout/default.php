<?php
/** @var string|null $title */

$title = $title ?? $this->trans('app.default_title');
$bodyClass = 'home-layout bg-light';
$primaryLinks = [
    ['href' => $this->locale_url(null, null, 'home'), 'label' => $this->trans('layout.nav.public_home')],
];
?>
<!DOCTYPE html>
<html lang="<?= $this->e($this->current_locale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="api-base" content="<?= $this->e($_ENV['API_BASE'] ?? '/') ?>">
    <title><?= $this->e($title) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= $this->vite_assets('home') ?>
    <?= $this->section('head') ?>
</head>
<body class="<?= $this->e($bodyClass) ?>">
    <nav class="navbar navbar-expand-md navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold text-primary" href="<?= $this->e($this->locale_url()) ?>">
                <i class="fa-solid fa-bag-shopping me-2" aria-hidden="true"></i><?= $this->e($this->trans('app.name')) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="<?= $this->e($this->trans('layout.nav.toggle')) ?>">
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
                </ul>
            </div>
        </div>
    </nav>

    <main class="container pt-4 pb-5">
        <?= $this->section('content') ?>
    </main>

    <footer class="border-0 text-sm text-muted text-center pb-4">
        <div class="container">
            <?= $this->e($this->trans('layout.footer.demo_note')) ?>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
<?= $this->section('scripts') ?>
</body>
</html>
