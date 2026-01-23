<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $groups */
/** @var string $search */
/** @var array<int, string> $granted */
/** @var int $totalPermissions */
/** @var \App\Integration\Flash\FlashMessages|null $flash */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.permissions.meta_title'),
    'user' => $user ?? null,
]);

$groups = $groups ?? [];
$search = $search ?? '';
$granted = $granted ?? [];
$totalPermissions = $totalPermissions ?? 0;
$flashMessages = $flash instanceof \App\Integration\Flash\FlashMessages ? $flash->getMessages() : [];
$deleteForms = [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.permissions.badge')) ?>
                </p>
                <h1 class="h3 mb-1">
                    <?= $this->e($this->trans('admin.permissions.heading')) ?>
                </h1>
                <p class="mb-0 text-secondary">
                    <?= $this->e($this->trans('admin.permissions.description')) ?>
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
                                'label' => $this->trans('layout.nav.admin_permissions'),
                            ],
                        ],
                    ]); ?>
                    <button class="btn btn-primary" type="submit" form="permission-matrix-form">
                        <i class="fa-solid fa-cloud-arrow-up me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.permissions.actions.publish')) ?>
                    </button>
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

<div class="card card-outline card-success mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-1">
                <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                <?= $this->e($this->trans('admin.permissions.create.title')) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= $this->e($this->trans('admin.permissions.create.subtitle')) ?>
            </p>
        </div>
    </div>
    <form class="card-body" method="post">
        <input type="hidden" name="action" value="create_permission">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-muted small" for="permission-key">
                    <?= $this->e($this->trans('admin.permissions.create.key')) ?>
                </label>
                <input
                    id="permission-key"
                    name="permission_key"
                    type="text"
                    class="form-control"
                    placeholder="orders.create"
                    required
                >
            </div>
            <div class="col-md-5">
                <label class="form-label text-muted small" for="permission-label">
                    <?= $this->e($this->trans('admin.permissions.create.label')) ?>
                </label>
                <input
                    id="permission-label"
                    name="permission_label"
                    type="text"
                    class="form-control"
                    placeholder="Create orders"
                >
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-success w-100" type="submit">
                    <?= $this->e($this->trans('admin.permissions.create.submit')) ?>
                </button>
            </div>
        </div>
    </form>
</div>

<form id="permission-matrix-form" method="get">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>
                <?= $this->e($this->trans('admin.permissions.search.title')) ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label text-muted small" for="q">
                        <?= $this->e($this->trans('admin.permissions.search.label')) ?>
                    </label>
                    <input
                        type="search"
                        class="form-control"
                        id="q"
                        name="q"
                        value="<?= $this->e($search) ?>"
                        placeholder="orders, refunds, payouts"
                    >
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="card-title mb-1">
                    <?= $this->e($this->trans('admin.permissions.groups.title')) ?>
                </h3>
                <p class="text-muted small mb-0">
                    <?= $this->e($this->trans('admin.permissions.groups.subtitle', [
                        '%enabled%' => (string) count($granted),
                        '%total%' => (string) $totalPermissions,
                    ])) ?>
                </p>
            </div>
            <button class="btn btn-outline-secondary" type="submit">
                <?= $this->e($this->trans('admin.permissions.actions.apply')) ?>
            </button>
        </div>
        <div class="card-body">
            <?php if ($groups === []): ?>
                <p class="text-muted mb-0">
                    <?= $this->e($this->trans('admin.permissions.groups.empty')) ?>
                </p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($groups as $group): ?>
                        <div class="col-md-6">
                            <article class="border rounded h-100 p-3">
                                <header class="mb-3">
                                    <h4 class="h6 mb-1">
                                        <?= $this->e($group['label'] ?? '') ?>
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        <?= $this->e($group['description'] ?? '') ?>
                                    </p>
                                </header>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach (($group['permissions'] ?? []) as $permission): ?>
                                        <?php $key = (string) ($permission['key'] ?? ''); ?>
                                        <?php $deleteFormId = 'delete-permission-' . md5($key); $deleteForms[$deleteFormId] = $key; ?>
                                        <li class="mb-2">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="granted[]"
                                                        id="perm-<?= $this->e($key) ?>"
                                                        value="<?= $this->e($key) ?>"
                                                        <?= in_array($key, $granted, true) ? 'checked' : '' ?>
                                                    >
                                                    <label class="form-check-label" for="perm-<?= $this->e($key) ?>">
                                                        <?= $this->e($permission['label'] ?? '') ?>
                                                    </label>
                                                </div>
                                                <button
                                                    class="btn btn-sm btn-outline-danger ms-auto"
                                                    type="submit"
                                                    form="<?= $this->e($deleteFormId) ?>"
                                                    formmethod="post"
                                                    onclick="return confirm('<?= $this->e($this->trans('admin.permissions.delete_confirm')) ?>');"
                                                >
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (($group['permissions'] ?? []) === []): ?>
                                        <li class="text-muted small">
                                            <?= $this->e($this->trans('admin.permissions.groups.no_match')) ?>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php foreach ($deleteForms as $formId => $permissionKey): ?>
    <form id="<?= $this->e($formId) ?>" method="post" class="d-none">
        <input type="hidden" name="action" value="delete_permission">
        <input type="hidden" name="permission_key" value="<?= $this->e($permissionKey) ?>">
    </form>
<?php endforeach; ?>
