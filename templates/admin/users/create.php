<?php
/** @var array|null $user */
/** @var \Slim\Flash\Messages|null $flash */
/** @var array<int, array{key: string, name: string, description: string, critical: bool}> $roles */
/** @var array<int, string> $statuses */

use Slim\Flash\Messages;

$this->layout('layout::admin', [
    'title' => $this->trans('admin.users.meta_title'),
    'user' => $user ?? null,
]);

$flashMessages = $flash instanceof Messages ? $flash->getMessages() : [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.users.badge')) ?>
                </p>
                <h1 class="h3 mb-1">
                    <?= $this->e($this->trans('admin.users.heading')) ?>
                </h1>
                <p class="mb-0 text-secondary">
                    <?= $this->e($this->trans('admin.users.description')) ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column align-items-lg-end gap-2 text-lg-end">
                    <?php $this->insert('admin::partials/breadcrumbs', [
                        'items' => [
                            [
                                'label' => $this->trans('layout.nav.dashboard'),
                                'href' => $this->locale_url('admin', null, 'admin'),
                            ],
                            [
                                'label' => $this->trans('layout.nav.admin_users'),
                                'href' => $this->locale_url('admin/users', null, 'admin'),
                            ],
                            [
                                'label' => $this->trans('admin.users.actions.invite'),
                            ],
                        ],
                    ]); ?>
                    <a class="btn btn-outline-secondary" href="<?= $this->e($this->locale_url('admin/users', null, 'admin')) ?>" role="button">
                        <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('common.back')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($flashMessages !== []): ?>
    <div class="row mb-3">
        <div class="col-lg-12">
            <?php foreach ($flashMessages as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?= $this->e($type === 'error' || $type === 'admin_error' ? 'danger' : 'success') ?> alert-dismissible fade show" role="alert">
                        <?= $this->e($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= $this->e($this->trans('common.close')) ?>"></button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card card-outline card-success">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <p class="text-uppercase text-muted small mb-1">
                <?= $this->e($this->trans('admin.users.badge')) ?>
            </p>
            <h3 class="card-title mb-0">
                <i class="fa-solid fa-user-plus me-2" aria-hidden="true"></i>
                <?= $this->e($this->trans('admin.users.actions.invite')) ?>
            </h3>
            <p class="text-muted small mb-0"><?= $this->e($this->trans('admin.users.create.helper')) ?></p>
        </div>
        <a class="btn btn-outline-secondary" href="<?= $this->e($this->locale_url('admin/users', null, 'admin')) ?>">
            <?= $this->e($this->trans('admin.users.table.title')) ?>
        </a>
    </div>
    <div class="card-body">
        <form class="row g-3" method="post">
            <div class="col-md-6 col-lg-3">
                <label class="form-label text-muted small" for="create-email"><?= $this->e($this->trans('admin.users.create.fields.email')) ?></label>
                <input type="email" class="form-control" id="create-email" name="email" placeholder="user@example.com" required>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label text-muted small" for="create-password"><?= $this->e($this->trans('admin.users.create.fields.password')) ?></label>
                <input type="password" class="form-control" id="create-password" name="password" placeholder="••••••••" required>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label text-muted small" for="create-roles"><?= $this->e($this->trans('admin.users.create.fields.roles')) ?></label>
                <select class="form-select" id="create-roles" name="roles[]" multiple>
                    <?php if ($roles === []): ?>
                        <option value="" disabled><?= $this->e($this->trans('admin.roles.table.empty')) ?></option>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $this->e($role['key']) ?>">
                                <?= $this->e($role['name']) ?> (<?= $this->e($role['key']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class="text-muted small mb-0"><?= $this->e($this->trans('admin.users.create.fields.roles_help')) ?></p>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label text-muted small" for="create-status"><?= $this->e($this->trans('admin.users.create.fields.status')) ?></label>
                <select class="form-select" id="create-status" name="status">
                    <?php foreach ($statuses as $status): ?>
                        <?php $statusKey = strtolower((string) $status); ?>
                        <option value="<?= $this->e($status) ?>">
                            <?= $this->e($this->trans('admin.user_detail.status.' . $statusKey)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-user-plus me-2" aria-hidden="true"></i>
                    <?= $this->e($this->trans('admin.users.actions.invite')) ?>
                </button>
            </div>
        </form>
    </div>
</div>
