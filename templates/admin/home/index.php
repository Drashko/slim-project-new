<?php
/** @var array|null $user */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.dashboard.meta_title'),
    'user' => $user ?? null,
]);
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.dashboard.badge')) ?>
                </p>
                <h1 class="h3 mb-1"><?= $this->e($this->trans('admin.dashboard.heading')) ?></h1>
                <p class="mb-0 text-secondary">
                    <?= $this->trans('admin.dashboard.intro', ['%role%' => '<code>ROLE_ADMIN</code>']) ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex flex-column align-items-lg-end gap-2 text-lg-end">
                    <?php $this->insert('admin::partials/breadcrumbs', [
                        'items' => [
                            [
                                'label' => $this->trans('layout.nav.dashboard'),
                                'href' => $this->locale_url('admin', null, 'admin'),
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-lg-8">
        <?php if (!empty($user)): ?>
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-user-shield me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.dashboard.session.title')) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted text-sm mb-1"><?= $this->e($this->trans('admin.dashboard.session.email')) ?></p>
                            <p class="fw-semibold mb-3"><?= $this->e($user['email'] ?? '') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted text-sm mb-1"><?= $this->e($this->trans('admin.dashboard.session.roles')) ?></p>
                            <p class="mb-3"><code><?= $this->e(isset($user['roles']) ? implode(', ', (array) $user['roles']) : '') ?></code></p>
                        </div>
                    </div>
                    <div class="alert <?= $this->can('admin.users.manage', $user ?? null) ? 'alert-success' : 'alert-warning' ?> mb-0">
                        <?php if ($this->can('admin.users.manage', $user ?? null)): ?>
                            <?= $this->e($this->trans('admin.dashboard.permissions.manage_users')) ?>
                        <?php else: ?>
                            <?= $this->e($this->trans('admin.dashboard.permissions.read_only')) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-list-check me-2" aria-hidden="true"></i>
                    <?= $this->e($this->trans('admin.dashboard.next_steps.title')) ?>
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <?= $this->e($this->trans('admin.dashboard.next_steps.description')) ?>
                </p>
                <ol class="list-group list-group-numbered mb-0">
                    <li class="list-group-item">
                        <?= $this->trans('admin.dashboard.next_steps.users', [
                            '%register%' => '<a href="' . $this->locale_url('auth/register') . '" class="link-primary">' . $this->trans('layout.account.create') . '</a>',
                        ]) ?>
                    </li>
                    <li class="list-group-item">
                        <?= $this->e($this->trans('admin.dashboard.next_steps.database')) ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><?= $this->e($this->trans('admin.dashboard.quick_actions.title')) ?></h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <?= $this->e($this->trans('admin.dashboard.quick_actions.description')) ?>
                </p>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action" href="<?= $this->e($this->locale_url('admin/users', null, 'admin')) ?>">
                        <?= $this->e($this->trans('admin.dashboard.quick_actions.manage_users')) ?>
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= $this->e($this->locale_url('admin/roles', null, 'admin')) ?>">
                        <?= $this->e($this->trans('admin.dashboard.quick_actions.manage_roles')) ?>
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= $this->e($this->locale_url('admin/permissions', null, 'admin')) ?>">
                        <?= $this->e($this->trans('admin.dashboard.quick_actions.manage_permissions')) ?>
                    </a>
                    <a class="list-group-item list-group-item-action" href="<?= $this->e($this->locale_url('admin/audit', null, 'admin')) ?>">
                        <?= $this->e($this->trans('admin.dashboard.quick_actions.view_audit')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
