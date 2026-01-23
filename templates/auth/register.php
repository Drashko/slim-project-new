<?php

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormView;

/** @var FormView $form */
/** @var array|null $user */
/** @var \App\Integration\Flash\FlashMessages $flash */

$this->layout('layout::public', [
    'title' => $this->trans('auth.register.meta_title'),
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
$confirmField = $formView['confirmPassword'] ?? null;
$accountField = $formView['accountType'] ?? null;
$tokenField = $formView['_token'] ?? null;
?>
<div class="row justify-content-center">
    <div class="col-lg-6 col-xl-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <header class="mb-4">
                    <h1 class="h3 mb-2"><?= $this->e($this->trans('auth.register.heading')) ?></h1>
                    <p class="text-muted mb-0"><?= $this->e($this->trans('auth.register.subheading')) ?></p>
                </header>

                <?php foreach ($flashMessages as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                        <?php $alertClass = $type === 'error' ? 'danger' : 'info'; ?>
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

                <form method="<?= $this->e($formMethod) ?>" novalidate class="mt-4">
                    <?php if ($tokenField instanceof FormView): ?>
                        <input type="hidden" name="<?= $this->e($tokenField->vars['full_name'] ?? '') ?>" value="<?= $this->e($tokenField->vars['value'] ?? '') ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="<?= $this->e($emailField->vars['id'] ?? 'email') ?>"><?= $this->e($this->trans('auth.register.email_label')) ?></label>
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
                    <div class="mb-3">
                        <label class="form-label" for="<?= $this->e($passwordField->vars['id'] ?? 'password') ?>"><?= $this->e($this->trans('auth.register.password_label')) ?></label>
                        <input
                            class="form-control <?= $hasErrors($passwordField) ? 'is-invalid' : '' ?>"
                            id="<?= $this->e($passwordField->vars['id'] ?? 'password') ?>"
                            type="password"
                            name="<?= $this->e($passwordField->vars['full_name'] ?? 'password') ?>"
                            autocomplete="<?= $this->e($passwordField->vars['attr']['autocomplete'] ?? 'new-password') ?>"
                            required
                        >
                        <?php foreach ($collectErrors($passwordField) as $error): ?>
                            <div class="invalid-feedback"><?= $this->e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="<?= $this->e($confirmField->vars['id'] ?? 'confirm_password') ?>"><?= $this->e($this->trans('auth.register.confirm_password_label')) ?></label>
                        <input
                            class="form-control <?= $hasErrors($confirmField) ? 'is-invalid' : '' ?>"
                            id="<?= $this->e($confirmField->vars['id'] ?? 'confirm_password') ?>"
                            type="password"
                            name="<?= $this->e($confirmField->vars['full_name'] ?? 'confirm_password') ?>"
                            autocomplete="<?= $this->e($confirmField->vars['attr']['autocomplete'] ?? 'new-password') ?>"
                            required
                        >
                        <?php foreach ($collectErrors($confirmField) as $error): ?>
                            <div class="invalid-feedback"><?= $this->e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="<?= $this->e($accountField->vars['id'] ?? 'account_type') ?>"><?= $this->e($this->trans('auth.register.account_type.label')) ?></label>
                        <select
                            class="form-select <?= $hasErrors($accountField) ? 'is-invalid' : '' ?>"
                            id="<?= $this->e($accountField->vars['id'] ?? 'account_type') ?>"
                            name="<?= $this->e($accountField->vars['full_name'] ?? 'account_type') ?>"
                        >
                            <?php foreach (($accountField->vars['choices'] ?? []) as $choice): ?>
                                <?php if (!$choice instanceof ChoiceView) { continue; } ?>
                                <option value="<?= $this->e($choice->value) ?>" <?= ((string) ($accountField->vars['value'] ?? '')) === (string) $choice->value ? 'selected' : '' ?>>
                                    <?= $this->e($this->trans((string) $choice->label)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php foreach ($collectErrors($accountField) as $error): ?>
                            <div class="invalid-feedback"><?= $this->e($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-primary w-100" type="submit"><?= $this->e($this->trans('auth.register.submit')) ?></button>
                </form>

                <p class="text-muted mt-3 mb-0"><?= $this->trans('auth.register.login_prompt', [
                        '%login%' => '<a href="' . $this->locale_url('auth/login') . '">' . $this->trans('layout.nav.profile_login') . '</a>',
                    ]) ?></p>
            </div>
        </div>
    </div>
</div>
