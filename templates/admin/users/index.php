<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $directory */
/** @var array{query: string, role: string, status: string} $filters */
/** @var array<int, string> $roles */
/** @var array<int, string> $statuses */
/** @var int $totalUsers */
/** @var array{page: int, perPage: int, total: int, totalPages: int, hasPrev: bool, hasNext: bool, from: int, to: int} $pagination */
/** @var array<string, mixed> $queryParams */
/** @var \Slim\Flash\Messages|null $flash */

use Slim\Flash\Messages;

$this->layout('layout::admin', [
    'title' => $this->trans('admin.users.meta_title'),
    'user' => $user ?? null,
]);

$directory = $directory ?? [];
$filters = $filters ?? ['query' => '', 'role' => 'all', 'status' => 'all'];
$roles = $roles ?? [];
$statuses = $statuses ?? [];
$pagination = $pagination ?? [
    'page' => 1,
    'perPage' => 50,
    'total' => 0,
    'totalPages' => 1,
    'hasPrev' => false,
    'hasNext' => false,
    'from' => 0,
    'to' => 0,
];
$queryParams = $queryParams ?? [];
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
                            ],
                        ],
                    ]); ?>
                    <a class="btn btn-primary" href="<?= $this->e($this->locale_url('admin/users/new', null, 'admin')) ?>" role="button">
                        <i class="fa-solid fa-user-plus me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.users.actions.invite')) ?>
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

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-filter me-2" aria-hidden="true"></i>
            <?= $this->e($this->trans('admin.users.filters.title')) ?>
        </h3>
    </div>
    <div class="card-body">
        <form class="row g-3" method="get">
            <div class="col-md-6 col-lg-4">
                <label class="form-label text-muted small" for="query">
                    <?= $this->e($this->trans('admin.users.filters.search')) ?>
                </label>
                <input
                    type="search"
                    class="form-control"
                    id="query"
                    name="query"
                    value="<?= $this->e($filters['query']) ?>"
                    placeholder="aditi@shop.co"
                >
            </div>
            <div class="col-md-3 col-lg-4">
                <label class="form-label text-muted small" for="role">
                    <?= $this->e($this->trans('admin.users.filters.role')) ?>
                </label>
                <select class="form-select" id="role" name="role">
                    <option value="all"<?= $filters['role'] === 'all' ? ' selected' : '' ?>>
                        <?= $this->e($this->trans('admin.users.filters.role_all')) ?>
                    </option>
                    <?php foreach ($roles as $role): ?>
                        <?php $value = (string) $role; ?>
                        <option value="<?= $this->e($value) ?>"<?= strcasecmp($filters['role'], $value) === 0 ? ' selected' : '' ?>>
                            <?= $this->e($value) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-lg-4">
                <label class="form-label text-muted small" for="status">
                    <?= $this->e($this->trans('admin.users.filters.status')) ?>
                </label>
                <select class="form-select" id="status" name="status">
                    <option value="all"<?= $filters['status'] === 'all' ? ' selected' : '' ?>>
                        <?= $this->e($this->trans('admin.users.filters.status_all')) ?>
                    </option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= $this->e($status) ?>"<?= strcasecmp($filters['status'], $status) === 0 ? ' selected' : '' ?>>
                            <?= $this->e($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-outline-secondary" type="submit">
                    <?= $this->e($this->trans('admin.users.filters.submit')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-0">
                <i class="fa-solid fa-address-book me-2" aria-hidden="true"></i>
                <?= $this->e($this->trans('admin.users.table.title')) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= $this->e($this->trans('admin.users.table.subtitle', ['%count%' => (string) $totalUsers])) ?>
            </p>
        </div>
        <button class="btn btn-outline-secondary" type="button">
            <i class="fa-solid fa-file-export me-2" aria-hidden="true"></i>
            <?= $this->e($this->trans('admin.users.actions.export')) ?>
        </button>
    </div>
    <div class="card-body p-0">
        <?php if ($directory === []): ?>
            <p class="text-center text-muted py-5 mb-0">
                <?= $this->e($this->trans('admin.users.table.empty')) ?>
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= $this->e($this->trans('admin.users.table.name')) ?></th>
                            <th><?= $this->e($this->trans('admin.users.table.role')) ?></th>
                            <th><?= $this->e($this->trans('admin.users.table.status')) ?></th>
                            <th><?= $this->e($this->trans('admin.users.table.last_login')) ?></th>
                            <th><?= $this->e($this->trans('admin.users.table.permissions')) ?></th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($directory as $member): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= $this->e($member['name'] ?? '') ?></div>
                                    <div class="text-muted small"><?= $this->e($member['email'] ?? '') ?></div>
                                </td>
                                <td><?= $this->e($member['role'] ?? '') ?></td>
                                <td>
                                    <?php $status = (string) ($member['status'] ?? ''); ?>
                                    <?php
                                    $statusClass = match (strtolower($status)) {
                                        'active' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'suspended' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $this->e($statusClass) ?>">
                                        <?= $this->e($status) ?>
                                    </span>
                                </td>
                                <td><?= $this->e($member['last_login'] ?? '—') ?></td>
                                <td>
                                    <?php foreach (($member['permissions'] ?? []) as $permission): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            <?= $this->e($permission) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-end">
                                    <a
                                        class="btn btn-sm btn-outline-primary"
                                        href="<?= $this->e($this->locale_url('admin/users/' . rawurlencode((string) ($member['id'] ?? '')), null, 'admin')) ?>"
                                    >
                                        <?= $this->e($this->trans('admin.users.table.manage')) ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php $this->insert('admin::partials/pagination', [
                'pagination' => $pagination,
                'baseUrl' => $this->locale_url('admin/users', null, 'admin'),
                'queryParams' => $queryParams,
            ]); ?>
        <?php endif; ?>
    </div>
</div>
