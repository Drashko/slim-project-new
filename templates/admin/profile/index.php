<?php
/** @var array|null $user */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.profile.meta_title'),
    'user' => $user ?? null,
]);

$roles = array_map(static fn($role) => (string) $role, (array) ($user['roles'] ?? []));
?>

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <p class="text-uppercase text-secondary small mb-1"><?= $this->e($this->trans('admin.badge')) ?></p>
                <h1 class="h4 mb-1"><?= $this->e($this->trans('admin.profile.heading')) ?></h1>
                <p class="text-muted mb-0"><?= $this->e($this->trans('admin.profile.intro')) ?></p>
            </div>
            <div class="card-body">
                <dl class="row mb-4">
                    <dt class="col-sm-4 text-muted"><?= $this->e($this->trans('admin.profile.session.email')) ?></dt>
                    <dd class="col-sm-8"><?= $this->e($user['email'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted"><?= $this->e($this->trans('admin.profile.session.roles')) ?></dt>
                    <dd class="col-sm-8"><?= $this->e($roles === [] ? '—' : implode(', ', $roles)) ?></dd>
                </dl>
                <div class="btn-group flex-wrap w-100" role="group">
                    <a class="btn btn-primary" href="<?= $this->e($this->locale_url('admin', null, 'admin')) ?>"><?= $this->e($this->trans('admin.profile.cta_dashboard')) ?></a>
                    <a class="btn btn-outline-primary" href="<?= $this->e($this->locale_url('admin/logout', null, 'admin')) ?>"><?= $this->e($this->trans('admin.profile.cta_logout')) ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
