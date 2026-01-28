<?php

use Symfony\Component\Form\FormView;

/** @var array|null $tokens */
/** @var array|null $user */
/** @var FormView $form */
/** @var \App\Integration\Flash\FlashMessages $flash */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.login.meta_title'),
    'user' => $user ?? null,
]);

$flashMessages = $flash->getMessages();
$formView = $form ?? null;

$collectErrors = static function (?FormView $field): array {
    if ($field === null) {
        return [];
    }

    $messages = [];
    foreach ($field->vars['errors'] ?? [] as $error) {
        $messages[] = $error->getMessage();
    }

    return $messages;
};

$hasErrors = static function (?FormView $field) use ($collectErrors): bool {
    return $collectErrors($field) !== [];
};

$formMethod = strtolower((string) ($formView->vars['method'] ?? 'post')) === 'get' ? 'get' : 'post';
$emailField = $formView['email'] ?? null;
$passwordField = $formView['password'] ?? null;
$tokenField = $formView['_token'] ?? null;
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

                <?php $globalErrors = $collectErrors($formView); ?>
                <?php if ($globalErrors !== []): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php foreach ($globalErrors as $error): ?>
                            <div><?= $this->e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="<?= $this->e($formMethod) ?>" name="admin_login" novalidate>
                    <?php if ($tokenField instanceof FormView): ?>
                        <input type="hidden" name="<?= $this->e($tokenField->vars['full_name'] ?? '') ?>" value="<?= $this->e($tokenField->vars['value'] ?? '') ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="<?= $this->e($emailField->vars['id'] ?? 'email') ?>"><?= $this->e($this->trans('admin.login.email_label')) ?></label>
                        <input
                            class="form-control <?= $hasErrors($emailField) ? 'is-invalid' : '' ?>"
                            id="<?= $this->e($emailField->vars['id'] ?? 'email') ?>"
                            type="email"
                            name="<?= $this->e($emailField->vars['full_name'] ?? 'email') ?>"
                            value="<?= $this->e((string) ($emailField->vars['value'] ?? '')) ?>"
                            autocomplete="<?= $this->e($emailField->vars['attr']['autocomplete'] ?? 'email') ?>"
                            required
                        >
                        <?php foreach ($collectErrors($emailField) as $error): ?>
                            <div class="invalid-feedback"><?= $this->e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="<?= $this->e($passwordField->vars['id'] ?? 'password') ?>"><?= $this->e($this->trans('admin.login.password_label')) ?></label>
                        <input
                            class="form-control <?= $hasErrors($passwordField) ? 'is-invalid' : '' ?>"
                            id="<?= $this->e($passwordField->vars['id'] ?? 'password') ?>"
                            type="password"
                            name="<?= $this->e($passwordField->vars['full_name'] ?? 'password') ?>"
                            autocomplete="<?= $this->e($passwordField->vars['attr']['autocomplete'] ?? 'current-password') ?>"
                            required
                        >
                        <?php foreach ($collectErrors($passwordField) as $error): ?>
                            <div class="invalid-feedback"><?= $this->e($error) ?></div>
                        <?php endforeach; ?>
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
