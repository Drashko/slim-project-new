<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $roles */
/** @var array<string, mixed> $selectedRole */
/** @var string $selectedId */
/** @var array<int, array<string, mixed>> $permissionGroups */
/** @var array<int, string> $selectedPermissions */
/** @var Messages|null $flash */

use Slim\Flash\Messages;

$this->layout('layout::admin', [
    'title' => $this->trans('admin.roles.meta_title'),
    'user' => $user ?? null,
]);

$roles = $roles ?? [];
$selectedRole = $selectedRole ?? null;
$permissionGroups = $permissionGroups ?? [];
$selectedPermissions = $selectedPermissions ?? [];
$flashMessages = $flash instanceof Messages ? $flash->getMessages() : [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.roles.badge')) ?>
                </p>
                <h1 class="h3 mb-1">
                    <?= $this->e($this->trans('admin.roles.heading')) ?>
                </h1>
                <p class="mb-0 text-secondary">
                    <?= $this->e($this->trans('admin.roles.description')) ?>
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
                            ],
                        ],
                    ]); ?>
                    <a class="btn btn-primary" href="<?= $this->e($this->locale_url('admin/roles/new', null, 'admin')) ?>" role="button">
                        <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.roles.actions.create')) ?>
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

<div class="row">
    <div class="col-xl-5">
        <div class="card card-outline card-secondary h-100">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fa-solid fa-layer-group me-2" aria-hidden="true"></i>
                    <?= $this->e($this->trans('admin.roles.library.title')) ?>
                </h3>
                <p class="text-muted small mb-0">
                    <?= $this->e($this->trans('admin.roles.library.subtitle')) ?>
                </p>
            </div>
            <div class="card-body">
                <div class="list-group role-list" role="tablist">
                    <?php foreach ($roles as $role): ?>
                        <?php $isActive = ($role['id'] ?? '') === $selectedId; ?>
                        <a
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?= $isActive ? 'active' : '' ?>"
                            href="?role=<?= $this->e($role['id'] ?? '') ?>"
                        >
                            <div>
                                <h4 class="h6 mb-1">
                                    <?= $this->e($role['name'] ?? '') ?>
                                </h4>
                                <p class="text-muted small mb-0">
                                    <?= $this->e($role['description'] ?? '') ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark mb-1">
                                    <?= $this->e((string) ($role['members'] ?? 0)) ?>
                                    <?= $this->e($this->trans('admin.roles.library.members')) ?>
                                </span>
                                <?php if (!empty($role['critical'])): ?>
                                    <div>
                                        <span class="badge bg-danger">
                                            <?= $this->e($this->trans('admin.roles.library.critical')) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="card card-outline card-primary mt-4">
            <div class="card-body d-flex flex-column gap-2">
                <h3 class="h5 mb-1">
                    <?= $this->e($this->trans('admin.roles.create.title')) ?>
                </h3>
                <p class="text-muted mb-0">
                    <?= $this->e($this->trans('admin.roles.create.subtitle')) ?>
                </p>
                <div>
                    <a class="btn btn-primary" href="<?= $this->e($this->locale_url('admin/roles/new', null, 'admin')) ?>">
                        <i class="fa-solid fa-plus me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.roles.actions.create')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card card-outline card-primary h-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="card-title mb-1">
                        <?= $this->e($selectedRole['name'] ?? '') ?>
                    </h3>
                    <p class="text-muted small mb-0">
                        <?= $this->e($this->trans('admin.roles.detail.count', ['%count%' => (string) count($selectedRole['permissions'] ?? [])])) ?>
                    </p>
                </div>
                <?php if (($selectedRole['id'] ?? '') !== ''): ?>
                    <form method="post" onsubmit="return confirm('<?= $this->e($this->trans('admin.roles.actions.delete_confirm')) ?>');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="role" value="<?= $this->e($selectedRole['id'] ?? '') ?>">
                        <button class="btn btn-outline-danger" type="submit" <?= !empty($selectedRole['critical']) ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-trash me-2" aria-hidden="true"></i>
                            <?= $this->e($this->trans('admin.roles.actions.delete')) ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (($selectedRole['id'] ?? '') === ''): ?>
                    <p class="text-muted mb-0">
                        <?= $this->e($this->trans('admin.roles.detail.empty')) ?>
                    </p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="update_permissions">
                        <input type="hidden" name="role" value="<?= $this->e($selectedRole['id'] ?? '') ?>">
                        <div class="row g-4">
                            <?php foreach ($permissionGroups as $group): ?>
                                <div class="col-md-6">
                                    <article class="border rounded h-100 p-3">
                                        <header class="mb-2">
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
                                                <li class="mb-2">
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            id="perm-<?= $this->e($key) ?>"
                                                            value="<?= $this->e($key) ?>"
                                                            <?= in_array($key, $selectedPermissions, true) ? 'checked' : '' ?>
                                                        >
                                                        <label class="form-check-label" for="perm-<?= $this->e($key) ?>">
                                                            <?= $this->e($permission['label'] ?? '') ?>
                                                        </label>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if (($group['permissions'] ?? []) === []): ?>
                                                <li class="text-muted small">
                                                    <?= $this->e($this->trans('admin.roles.detail.no_permissions')) ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="text-muted small mb-0">
                                <?= $this->e($this->trans('admin.roles.detail.helper')) ?>
                            </p>
                            <button class="btn btn-primary" type="submit">
                                <?= $this->e($this->trans('admin.roles.actions.save_permissions')) ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
