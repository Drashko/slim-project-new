<?php
/** @var array|null $user */
/** @var array<int, array{key: string, name: string, description: string, critical: bool}> $roles */
/** @var array<string, mixed>|null $member */
/** @var array<string, string> $contact */
/** @var array<int, array<string, string>> $timeline */
/** @var array<int, array<string, string>> $activity */

$hasMember = $member !== null;
$memberName = (string) ($member['name'] ?? $this->trans('admin.user_detail.not_found.title'));

$this->layout('layout::admin', [
    'title' => $hasMember
        ? $this->trans('admin.user_detail.meta_title', ['%name%' => $memberName])
        : $this->trans('admin.user_detail.not_found.meta_title'),
    'user' => $user ?? null,
]);

$contact = $contact ?? [];
$timeline = $timeline ?? [];
$activity = $activity ?? [];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1">
                    <?= $this->e($this->trans('admin.user_detail.badge')) ?>
                </p>
                <h1 class="h3 mb-1">
                    <?= $this->e($hasMember ? $memberName : $this->trans('admin.user_detail.heading')) ?>
                </h1>
                <p class="mb-0 text-secondary">
                    <?= $this->e($hasMember ? $this->trans('admin.user_detail.intro') : $this->trans('admin.user_detail.not_found.description')) ?>
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
                            [
                                'label' => $this->trans('layout.nav.admin_users'),
                                'href' => $this->locale_url('admin/users', null, 'admin'),
                            ],
                            [
                                'label' => $hasMember ? $memberName : $this->trans('admin.user_detail.heading'),
                            ],
                        ],
                    ]); ?>
                    <a class="btn btn-outline-secondary" href="<?= $this->e($this->locale_url('admin/users', null, 'admin')) ?>" role="button">
                        <i class="fa-solid fa-arrow-left-long me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.user_detail.actions.back')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!$hasMember): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-user-slash fa-2x text-muted mb-3" aria-hidden="true"></i>
            <p class="lead mb-2">
                <?= $this->e($this->trans('admin.user_detail.not_found.title')) ?>
            </p>
            <p class="text-muted mb-0">
                <?= $this->e($this->trans('admin.user_detail.not_found.description')) ?>
            </p>
        </div>
    </div>
<?php else: ?>
    <?php $status = strtolower((string) ($member['status'] ?? 'unknown')); ?>
    <?php
    $statusClass = match ($status) {
        'active' => 'bg-success',
        'pending' => 'bg-warning text-dark',
        'suspended' => 'bg-danger',
        default => 'bg-secondary',
    };
    ?>
    <div class="row">
            <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fa-solid fa-id-badge me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.user_detail.overview.title')) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <form class="row g-3 mb-4" method="post">
                        <div class="col-md-6">
                            <label class="form-label text-muted small" for="update-email"><?= $this->e($this->trans('admin.user_detail.form.email')) ?></label>
                            <input type="email" class="form-control" id="update-email" name="email" value="<?= $this->e((string) ($member['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small" for="update-password"><?= $this->e($this->trans('admin.user_detail.form.password')) ?></label>
                            <input type="password" class="form-control" id="update-password" name="password" placeholder="<?= $this->e($this->trans('admin.user_detail.form.password_placeholder')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small" for="update-roles"><?= $this->e($this->trans('admin.user_detail.form.roles')) ?></label>
                            <?php $memberRoles = array_map('strtoupper', (array) ($member['permissions'] ?? [])); ?>
                            <select class="form-select" id="update-roles" name="roles[]" multiple>
                                <?php if ($roles === []): ?>
                                    <option value="" disabled><?= $this->e($this->trans('admin.roles.table.empty')) ?></option>
                                <?php else: ?>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $this->e($role['key']) ?>"<?= in_array($role['key'], $memberRoles, true) ? ' selected' : '' ?>>
                                            <?= $this->e($role['name']) ?> (<?= $this->e($role['key']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                            </select>
                            <p class="text-muted small mb-0"><?= $this->e($this->trans('admin.user_detail.form.roles_help')) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small" for="update-status"><?= $this->e($this->trans('admin.user_detail.form.status')) ?></label>
                            <?php $currentStatus = (string) ($member['status'] ?? 'Active'); ?>
                            <select class="form-select" id="update-status" name="status">
                                <option value="Active"<?= strcasecmp($currentStatus, 'Active') === 0 ? ' selected' : '' ?>><?= $this->e($this->trans('admin.user_detail.status.active')) ?></option>
                                <option value="Pending"<?= strcasecmp($currentStatus, 'Pending') === 0 ? ' selected' : '' ?>><?= $this->e($this->trans('admin.user_detail.status.pending')) ?></option>
                                <option value="Suspended"<?= strcasecmp($currentStatus, 'Suspended') === 0 ? ' selected' : '' ?>><?= $this->e($this->trans('admin.user_detail.status.suspended')) ?></option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                                <?= $this->e($this->trans('admin.user_detail.actions.save')) ?>
                            </button>
                        </div>
                    </form>

                    <form method="post" onsubmit="return confirm('<?= $this->e($this->trans('admin.user_detail.actions.delete_confirm')) ?>');">
                        <input type="hidden" name="_action" value="DELETE">
                        <button class="btn btn-outline-danger" type="submit">
                            <i class="fa-solid fa-user-slash me-2" aria-hidden="true"></i>
                            <?= $this->e($this->trans('admin.user_detail.actions.delete')) ?>
                        </button>
                    </form>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">
                                <?= $this->e($this->trans('admin.user_detail.overview.email')) ?>
                            </p>
                            <p class="fw-semibold mb-0">
                                <?= $this->e($member['email'] ?? '—') ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted small mb-1">
                                <?= $this->e($this->trans('admin.user_detail.overview.role')) ?>
                            </p>
                            <p class="fw-semibold mb-0">
                                <?= $this->e($member['role'] ?? '—') ?>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted small mb-1">
                                <?= $this->e($this->trans('admin.user_detail.overview.status')) ?>
                            </p>
                            <span class="badge <?= $this->e($statusClass) ?>">
                                <?= $this->e($this->trans('admin.user_detail.status.' . $status)) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">
                                <?= $this->e($this->trans('admin.user_detail.overview.last_login')) ?>
                            </p>
                            <p class="fw-semibold mb-0">
                                <?= $this->e($member['last_login'] ?? '—') ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">
                                <?= $this->e($this->trans('admin.user_detail.permissions.title')) ?>
                            </p>
                            <?php $permissions = (array) ($member['permissions'] ?? []); ?>
                            <?php if ($permissions === []): ?>
                                <p class="text-muted mb-0">
                                    <?= $this->e($this->trans('admin.user_detail.permissions.empty')) ?>
                                </p>
                            <?php else: ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($permissions as $permission): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= $this->e($permission) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary mt-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fa-solid fa-list-check me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.user_detail.activity.title')) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($activity === []): ?>
                        <p class="text-muted mb-0">
                            <?= $this->e($this->trans('admin.user_detail.activity.empty')) ?>
                        </p>
                    <?php else: ?>
                        <?php foreach ($activity as $entry): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h4 class="h6 mb-1">
                                            <?= $this->e($entry['title'] ?? '') ?>
                                        </h4>
                                        <p class="text-muted mb-0">
                                            <?= $this->e($entry['detail'] ?? '') ?>
                                        </p>
                                    </div>
                                    <span class="text-muted small ms-3">
                                        <?= $this->e($entry['timestamp'] ?? '') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fa-solid fa-address-card me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.user_detail.contact.title')) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-1">
                        <?= $this->e($this->trans('admin.user_detail.contact.phone')) ?>
                    </p>
                    <p class="fw-semibold mb-3">
                        <?= $this->e($contact['phone'] ?? '—') ?>
                    </p>
                    <p class="text-muted small mb-1">
                        <?= $this->e($this->trans('admin.user_detail.contact.location')) ?>
                    </p>
                    <p class="fw-semibold mb-0">
                        <?= $this->e($contact['location'] ?? '—') ?>
                    </p>
                </div>
            </div>
            <div class="card card-outline card-warning mt-4">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fa-solid fa-timeline me-2" aria-hidden="true"></i>
                        <?= $this->e($this->trans('admin.user_detail.timeline.title')) ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($timeline === []): ?>
                        <p class="text-muted mb-0">
                            <?= $this->e($this->trans('admin.user_detail.timeline.empty')) ?>
                        </p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($timeline as $entry): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="fw-semibold">
                                        <?= $this->e($this->trans('admin.user_detail.timeline.' . ($entry['key'] ?? 'unknown'))) ?>
                                    </span>
                                    <span class="text-muted small">
                                        <?= $this->e($entry['date'] ?? '') ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
