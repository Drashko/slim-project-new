<?php
/** @var array|null $user */

$this->layout('layout::public', [
    'title' => $this->trans('profile.meta_title'),
    'user' => $user ?? null,
]);

$roles = array_map(static fn($role) => (string) $role, (array) ($user['roles'] ?? []));
?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <header class="mb-4">
                    <p class="text-uppercase small text-secondary mb-1">Profile</p>
                    <h1 class="h3 mb-2"><?= $this->e($this->trans('profile.heading')) ?></h1>
                    <p class="text-muted mb-0"><?= $this->e($this->trans('profile.description')) ?></p>
                </header>

                <?php if ($user !== null): ?>
                    <p class="text-success fw-semibold"><?= $this->e($this->trans('profile.signed_in', [
                            '%email%' => $user['email'] ?? '',
                        ])) ?></p>
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary"><?= $this->e($this->trans('profile.session.email')) ?></dt>
                        <dd class="col-sm-8 mb-1"><?= $this->e($user['email'] ?? '') ?></dd>
                        <dt class="col-sm-4 text-secondary"><?= $this->e($this->trans('profile.session.roles')) ?></dt>
                        <dd class="col-sm-8 mb-0"><?= $this->e($roles === [] ? '—' : implode(', ', $roles)) ?></dd>
                    </dl>
                <?php else: ?>
                    <p class="text-muted mb-0"><?= $this->e($this->trans('profile.signed_out')) ?></p>
                    <div class="alert alert-info mt-3 mb-0" role="alert">
                        <?= $this->e($this->trans('profile.empty_state')) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4 d-flex flex-column">
                <h2 class="h5 mb-3"><?= $this->e($this->trans('profile.cards.profile_title')) ?></h2>
                <p class="text-muted mb-4"><?= $this->e($this->trans('profile.cards.profile_description')) ?></p>
                <div class="d-grid gap-2">
                    <a class="btn btn-primary" href="<?= $this->e($this->locale_url('profile/login')) ?>"><?= $this->e($this->trans('profile.actions.login')) ?></a>
                    <a class="btn btn-outline-primary" href="<?= $this->e($this->locale_url('profile/ads')) ?>"><?= $this->e($this->trans('profile.actions.ads')) ?></a>
                </div>
                <?php if ($user !== null): ?>
                    <a class="btn btn-link text-danger mt-3 align-self-start" href="<?= $this->e($this->locale_url('auth/logout')) ?>"><?= $this->e($this->trans('profile.actions.logout')) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
