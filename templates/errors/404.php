<?php
/** @var string|null $requestedPath */
/** @var array|null $user */

$this->layout('layout::public', [
    'title' => $this->trans('errors.not_found.meta_title'),
    'user' => $user ?? null,
]);

$requestedPath = $requestedPath ?? '';
?>

<div class="py-5 text-center">
    <div class="mb-3">
        <span class="badge text-bg-light text-uppercase">404</span>
    </div>
    <h1 class="display-5 fw-semibold mb-3"><?= $this->e($this->trans('errors.not_found.title')) ?></h1>
    <p class="lead text-muted mb-4"><?= $this->e($this->trans('errors.not_found.description')) ?></p>
    <?php if ($requestedPath !== ''): ?>
        <p class="small text-muted mb-4">
            <?= $this->e($this->trans('errors.not_found.requested_path', ['%path%' => $requestedPath])) ?>
        </p>
    <?php endif; ?>
    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
        <a class="btn btn-primary" href="<?= $this->e($this->locale_url(null, null, 'public')) ?>">
            <?= $this->e($this->trans('errors.not_found.action_home')) ?>
        </a>
        <a class="btn btn-outline-secondary" href="<?= $this->e($this->locale_url('auth/login', null, 'public')) ?>">
            <?= $this->e($this->trans('errors.not_found.action_login')) ?>
        </a>
    </div>
</div>
