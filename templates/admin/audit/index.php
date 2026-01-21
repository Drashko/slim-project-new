<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $logs */
/** @var int $total */
/** @var int $page */
/** @var int $pageSize */
/** @var array<string, string|null> $filters */

$filters = array_merge([
    'eventType' => null,
    'aggregateType' => null,
    'aggregateId' => null,
    'processed' => null,
], $filters ?? []);

$this->layout('layout::admin', [
    'title' => $this->trans('admin.audit.meta_title'),
    'user' => $user ?? null,
]);
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.audit.badge')) ?>
                </p>
                <h1 class="h3 mb-1"><?= $this->e($this->trans('admin.audit.heading')) ?></h1>
                <p class="text-secondary mb-0">
                    <?= $this->e($this->trans('admin.audit.description')) ?>
                </p>
            </div>
            <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                <div class="d-flex flex-column align-items-sm-end gap-2 text-sm-end">
                    <?php $this->insert('admin::partials/breadcrumbs', [
                        'items' => [
                            [
                                'label' => $this->trans('layout.nav.dashboard'),
                                'href' => $this->locale_url('admin', null, 'admin'),
                            ],
                            [
                                'label' => $this->trans('layout.nav.admin_audit'),
                            ],
                        ],
                    ]); ?>
                    <span class="badge bg-primary-subtle text-primary fw-semibold">
                        <?= $this->e($this->trans('admin.audit.total_records', ['%count%' => $total])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fa-solid fa-clipboard-list me-2" aria-hidden="true"></i>
            <?= $this->e($this->trans('admin.audit.table.title')) ?>
        </h3>
        <span class="text-muted small">
            <?= $this->e($this->trans('admin.audit.table.page_summary', [
                '%page%' => $page,
                '%page_size%' => $pageSize,
            ])) ?>
        </span>
    </div>
    <div class="card-body border-bottom">
        <form class="admin-filter-form" method="get">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="event_type_filter">
                        <?= $this->e($this->trans('admin.audit.filters.event_type')) ?>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="event_type_filter"
                        name="event_type"
                        placeholder="<?= $this->e($this->trans('admin.audit.filters.event_type_placeholder')) ?>"
                        value="<?= $this->e($filters['eventType'] ?? '') ?>"
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="aggregate_type_filter">
                        <?= $this->e($this->trans('admin.audit.filters.aggregate_type')) ?>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="aggregate_type_filter"
                        name="aggregate_type"
                        placeholder="<?= $this->e($this->trans('admin.audit.filters.aggregate_type_placeholder')) ?>"
                        value="<?= $this->e($filters['aggregateType'] ?? '') ?>"
                    >
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="aggregate_id_filter">
                        <?= $this->e($this->trans('admin.audit.filters.aggregate_id')) ?>
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        id="aggregate_id_filter"
                        name="aggregate_id"
                        placeholder="<?= $this->e($this->trans('admin.audit.filters.aggregate_id_placeholder')) ?>"
                        value="<?= $this->e($filters['aggregateId'] ?? '') ?>"
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="processed_filter">
                        <?= $this->e($this->trans('admin.audit.filters.status')) ?>
                    </label>
                    <select class="form-select" id="processed_filter" name="processed">
                        <option value=""<?= ($filters['processed'] ?? '') === null ? ' selected' : '' ?>>
                            <?= $this->e($this->trans('admin.audit.filters.status_all')) ?>
                        </option>
                        <option value="processed"<?= ($filters['processed'] ?? '') === 'processed' ? ' selected' : '' ?>>
                            <?= $this->e($this->trans('admin.audit.filters.status_processed')) ?>
                        </option>
                        <option value="pending"<?= ($filters['processed'] ?? '') === 'pending' ? ' selected' : '' ?>>
                            <?= $this->e($this->trans('admin.audit.filters.status_pending')) ?>
                        </option>
                    </select>
                </div>
                <div class="col-md-1 d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.audit.filters.actions.apply')) ?>
                    </button>
                    <a class="btn btn-outline-secondary" href="<?= $this->e($this->locale_url('admin/audit', null, 'admin')) ?>">
                        <?= $this->e($this->trans('admin.audit.filters.actions.reset')) ?>
                    </a>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if ($logs === []): ?>
            <div class="p-4 text-center text-muted">
                <?= $this->e($this->trans('admin.audit.empty')) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr>
                        <th class="admin-audit-col-event">
                            <?= $this->e($this->trans('admin.audit.table.event_type')) ?>
                        </th>
                        <th class="admin-audit-col-aggregate">
                            <?= $this->e($this->trans('admin.audit.table.aggregate')) ?>
                        </th>
                        <th class="admin-audit-col-id">
                            <?= $this->e($this->trans('admin.audit.table.occurred_at')) ?>
                        </th>
                        <th class="admin-audit-col-status">
                            <?= $this->e($this->trans('admin.audit.table.processed')) ?>
                        </th>
                        <th>
                            <?= $this->e($this->trans('admin.audit.table.payload')) ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark"><?= $this->e($log['eventType'] ?? '') ?></div>
                                <p class="small text-muted mb-0"><?= $this->e($log['aggregateType'] ?? '') ?></p>
                            </td>
                            <td>
                                <code class="small text-break"><?= $this->e($log['aggregateId'] ?? '') ?></code>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    <?= $this->e($log['occurredAt'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($log['processed'])): ?>
                                    <span class="badge bg-success">
                                        <?= $this->e($this->trans('admin.audit.table.status_processed')) ?>
                                    </span>
                                    <?php if (!empty($log['processedAt'])): ?>
                                        <div class="small text-muted mt-1">
                                            <?= $this->e($log['processedAt']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">
                                        <?= $this->e($this->trans('admin.audit.table.status_pending')) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-audit-col-payload">
                                <pre class="mb-0 small bg-light p-2 rounded text-break"><?= $this->e($log['payload'] ?? '{}') ?></pre>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
