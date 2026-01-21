<?php
/** @var array|null $user */
/** @var array<int, array<string, string>> $allPermissions */
/** @var \Slim\Flash\Messages|null $flash */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.roles.create.title'),
    'user' => $user ?? null,
]);

$allPermissions = $allPermissions ?? [];
$flashMessages = $flash instanceof \Slim\Flash\Messages ? $flash->getMessages() : [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.roles.badge')) ?>
                </p>
                <h1 class="h3 mb-1">
                    <?= $this->e($this->trans('admin.roles.create.title')) ?>
                </h1>
                <p class="mb-0 text-secondary">
                    <?= $this->e($this->trans('admin.roles.create.subtitle')) ?>
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
                                'label' => $this->trans('layout.nav.admin_roles'),
                                'href' => $this->locale_url('admin/roles', null, 'admin'),
                            ],
                            [
                                'label' => $this->trans('admin.roles.create.title'),
                            ],
                        ],
                    ]); ?>
                    <a class="btn btn-outline-secondary" href="<?= $this->e($this->locale_url('admin/roles', null, 'admin')) ?>">
                        <i class="fa-solid fa-layer-group me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('layout.nav.admin_roles')) ?>
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
                    <div class="alert alert-<?= $this->e($type === 'error' ? 'danger' : $type) ?> alert-dismissible fade show" role="alert">
                        <?= $this->e($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= $this->e($this->trans('common.close')) ?>"></button>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-1">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                <?= $this->e($this->trans('admin.roles.create.title')) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= $this->e($this->trans('admin.roles.create.subtitle')) ?>
            </p>
        </div>
        <span class="badge bg-light text-dark">
            <?= $this->e((string) count($allPermissions)) ?>
            <?= $this->e($this->trans('admin.roles.create.permissions_available')) ?>
        </span>
    </div>
    <form class="card-body" method="post">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted small" for="role-key">
                    <?= $this->e($this->trans('admin.roles.create.key')) ?>
                </label>
                <input
                    id="role-key"
                    name="role_key"
                    type="text"
                    class="form-control"
                    placeholder="ROLE_MANAGER"
                    required
                >
                <p class="text-muted small mb-0">
                    <?= $this->e($this->trans('admin.roles.create.key_help')) ?>
                </p>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small" for="role-name">
                    <?= $this->e($this->trans('admin.roles.create.name')) ?>
                </label>
                <input
                    id="role-name"
                    name="name"
                    type="text"
                    class="form-control"
                    placeholder="Store Manager"
                    required
                >
            </div>
            <div class="col-12">
                <label class="form-label text-muted small" for="role-description">
                    <?= $this->e($this->trans('admin.roles.create.description')) ?>
                </label>
                <textarea
                    id="role-description"
                    name="description"
                    rows="2"
                    class="form-control"
                    placeholder="<?= $this->e($this->trans('admin.roles.create.description_placeholder')) ?>"
                ></textarea>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small" for="role-permissions">
                    <?= $this->e($this->trans('admin.roles.create.permissions')) ?>
                </label>
                <select id="role-permissions" name="permissions[]" class="form-select" multiple size="6">
                    <?php foreach ($allPermissions as $permission): ?>
                        <option value="<?= $this->e($permission['key'] ?? '') ?>">
                            <?= $this->e($permission['label'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-muted small mb-0">
                    <?= $this->e($this->trans('admin.roles.create.permissions_help')) ?>
                </p>
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="critical" name="critical">
                    <label class="form-check-label" for="critical">
                        <?= $this->e($this->trans('admin.roles.create.critical')) ?>
                    </label>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <p class="text-muted small mb-0">
                <?= $this->e($this->trans('admin.roles.create.helper')) ?>
            </p>
            <button class="btn btn-primary" type="submit">
                <?= $this->e($this->trans('admin.roles.create.submit')) ?>
            </button>
        </div>
    </form>
</div>
