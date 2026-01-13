<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $ads */
/** @var array<string, mixed> $filters */
/** @var string[] $statuses */
/** @var \Slim\Flash\Messages|null $flash */

use Slim\Flash\Messages;

$this->layout('layout::admin', [
    'title' => $this->trans('admin.ads.meta_title'),
    'user' => $user ?? null,
]);

$flashMessages = $flash instanceof Messages ? $flash->getMessages() : [];
$ads = $ads ?? [];
$filters = $filters ?? [];
$statuses = $statuses ?? ['Pending', 'Published', 'Archived'];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.ads.badge')) ?>
                </p>
                <h1 class="h3 mb-1">
                    <?= $this->e($this->trans('admin.ads.heading')) ?>
                </h1>
                <p class="mb-0 text-secondary">
                    <?= $this->e($this->trans('admin.ads.description')) ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <?php $this->insert('admin::partials/breadcrumbs', [
                    'items' => [
                        [
                            'label' => $this->trans('layout.nav.dashboard'),
                            'href' => $this->locale_url('admin', null, 'admin'),
                        ],
                        [
                            'label' => $this->trans('layout.nav.admin_ads'),
                        ],
                    ],
                ]); ?>
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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title mb-0">
                <i class="fa-solid fa-bullhorn me-2" aria-hidden="true"></i>
                <?= $this->e($this->trans('admin.ads.table.title')) ?>
            </h3>
            <p class="text-muted small mb-0">
                <?= $this->e($this->trans('admin.ads.table.subtitle', ['%count%' => (string) count($ads)])) ?>
            </p>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="border-bottom p-3">
            <form class="row g-3 align-items-end" method="get">
                <div class="col-12">
                    <p class="text-muted small mb-0"><?= $this->e($this->trans('admin.ads.filters.title')) ?></p>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small" for="filter-category"><?= $this->e($this->trans('admin.ads.filters.category')) ?></label>
                    <input
                        class="form-control form-control-sm"
                        type="text"
                        id="filter-category"
                        name="category"
                        value="<?= $this->e((string) ($filters['category'] ?? '')) ?>"
                        placeholder="<?= $this->e($this->trans('admin.ads.filters.category_placeholder')) ?>"
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small" for="filter-status"><?= $this->e($this->trans('admin.ads.filters.status')) ?></label>
                    <select class="form-select form-select-sm" id="filter-status" name="status">
                        <option value=""><?= $this->e($this->trans('admin.ads.filters.status_placeholder')) ?></option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $this->e($status) ?>"<?= strcasecmp((string) ($filters['status'] ?? ''), $status) === 0 ? ' selected' : '' ?>>
                                <?= $this->e($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small" for="filter-from"><?= $this->e($this->trans('admin.ads.filters.from_date')) ?></label>
                    <input
                        class="form-control form-control-sm"
                        type="date"
                        id="filter-from"
                        name="from_date"
                        value="<?= $this->e((string) ($filters['from_date'] ?? '')) ?>"
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small" for="filter-to"><?= $this->e($this->trans('admin.ads.filters.to_date')) ?></label>
                    <input
                        class="form-control form-control-sm"
                        type="date"
                        id="filter-to"
                        name="to_date"
                        value="<?= $this->e((string) ($filters['to_date'] ?? '')) ?>"
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small" for="filter-user"><?= $this->e($this->trans('admin.ads.filters.user')) ?></label>
                    <input
                        class="form-control form-control-sm"
                        type="text"
                        id="filter-user"
                        name="user"
                        value="<?= $this->e((string) ($filters['user'] ?? '')) ?>"
                        placeholder="<?= $this->e($this->trans('admin.ads.filters.user_placeholder')) ?>"
                    >
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary btn-sm" type="submit">
                        <?= $this->e($this->trans('admin.ads.filters.apply')) ?>
                    </button>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= $this->e($this->locale_url('admin/ads', null, 'admin')) ?>">
                        <?= $this->e($this->trans('admin.ads.filters.reset')) ?>
                    </a>
                </div>
            </form>
        </div>
        <?php if ($ads === []): ?>
            <p class="text-center text-muted py-5 mb-0">
                <?= $this->e($this->trans('admin.ads.table.empty')) ?>
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th><?= $this->e($this->trans('admin.ads.table.title_header')) ?></th>
                        <th><?= $this->e($this->trans('admin.ads.table.category')) ?></th>
                        <th><?= $this->e($this->trans('admin.ads.table.status')) ?></th>
                        <th><?= $this->e($this->trans('admin.ads.table.created')) ?></th>
                        <th class="text-end"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ads as $ad): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= $this->e($ad['title'] ?? '') ?></div>
                                <div class="text-muted small"><?= $this->e($ad['id'] ?? '') ?></div>
                            </td>
                            <td><?= $this->e($ad['category'] ?? '') ?></td>
                            <td>
                                <?php $status = strtolower((string) ($ad['status'] ?? '')); ?>
                                <?php
                                $statusClass = match ($status) {
                                    'published' => 'bg-success',
                                    'archived' => 'bg-secondary',
                                    default => 'bg-warning text-dark',
                                };
                                ?>
                                <span class="badge <?= $this->e($statusClass) ?>">
                                    <?= $this->e(ucfirst((string) ($ad['status'] ?? 'Pending'))) ?>
                                </span>
                            </td>
                            <td><?= $this->e($ad['created_at'] ?? '') ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="<?= $this->e($this->locale_url('admin/ads/' . rawurlencode((string) ($ad['id'] ?? '')), null, 'admin')) ?>">
                                    <?= $this->e($this->trans('admin.ads.table.manage')) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
