<?php
/** @var array|null $tokens */
/** @var array|null $user */
/** @var string|null $last_email */
/** @var \Slim\Flash\Messages $flash */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.login.meta_title'),
    'user' => $user ?? null,
]);

$flashMessages = $flash->getMessages();
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-xl-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <p class="text-uppercase text-secondary small mb-1"><?= $this->e($this->trans('admin.badge')) ?></p>
                <h1 class="h4 mb-1"><?= $this->e($this->trans('admin.login.heading')) ?></h1>
                <p class="text-muted mb-0"><?= $this->e($this->trans('admin.login.subheading')) ?></p>
            </div>
            <div class="card-body">
                <?php foreach ($flashMessages as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <?php $alertClass = $type === 'error' ? 'danger' : ($type === 'success' ? 'success' : 'info'); ?>
                        <div class="alert alert-<?= $alertClass ?>" role="alert"><?= $this->e($message) ?></div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="admin-email"><?= $this->e($this->trans('admin.login.email_label')) ?></label>
                        <input class="form-control" id="admin-email" type="email" name="email" value="<?= $this->e($last_email ?? '') ?>" required autocomplete="email">
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="admin-password"><?= $this->e($this->trans('admin.login.password_label')) ?></label>
                        <input class="form-control" id="admin-password" type="password" name="password" required autocomplete="current-password">
                    </div>
                    <button class="btn btn-primary w-100" type="submit"><?= $this->e($this->trans('admin.login.submit')) ?></button>
                </form>

                <p class="text-muted small mt-3 mb-0"><?= $this->trans('admin.login.profile_prompt', [
                        '%profile_login%' => '<a href="' . $this->locale_url('profile/login') . '">' . $this->trans('profile.login.cta') . '</a>',
                    ]) ?></p>
            </div>
        </div>

        <?php if ($tokens !== null): ?>
            <div class="card card-outline card-secondary mt-4">
                <div class="card-header">
                    <h3 class="card-title text-uppercase text-sm mb-0"><?= $this->e($this->trans('auth.login.tokens.title')) ?></h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small"><?= $this->e($this->trans('auth.login.tokens.description')) ?></p>
                    <dl class="row gy-2 small mb-0">
                        <dt class="col-sm-4 text-secondary"><?= $this->e($this->trans('auth.login.tokens.access_token')) ?></dt>
                        <dd class="col-sm-8"><code><?= $this->e($tokens['access_token'] ?? '') ?></code></dd>
                        <dt class="col-sm-4 text-secondary"><?= $this->e($this->trans('auth.login.tokens.expires_at')) ?></dt>
                        <dd class="col-sm-8"><?= $this->e($tokens['expires_at'] ?? '') ?></dd>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
