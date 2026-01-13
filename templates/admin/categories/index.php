<?php
/** @var array|null $user */
/** @var \Slim\Flash\Messages|null $flash */
/** @var array<int, array{id: string, name: string, parent_id: string|null, parent_name: string|null}> $categories */
/** @var array<int, array{id: string, label: string}> $parentOptions */
/** @var array{id: string, name: string, parent_id: string|null, parent_name: string|null}|null $selected */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.categories.meta_title'),
    'user' => $user ?? null,
]);

$selected = $selected ?? null;
$selectedId = $selected['id'] ?? null;
$selectedParentId = $selected['parent_id'] ?? '';
$flash = $flash ?? null;
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-8">
                <p class="text-uppercase text-muted small mb-1"><?= $this->e($this->trans('admin.categories.badge')) ?></p>
                <h1 class="h3 mb-1"><?= $this->e($this->trans('admin.categories.heading')) ?></h1>
                <p class="mb-0 text-secondary"><?= $this->e($this->trans('admin.categories.description')) ?></p>
            </div>
            <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                <?php $this->insert('admin::partials/breadcrumbs', [
                    'items' => [
                        [
                            'href' => $this->locale_url('admin', null, 'admin'),
                            'label' => $this->trans('layout.nav.dashboard'),
                        ],
                        [
                            'href' => $this->locale_url('admin/categories', null, 'admin'),
                            'label' => $this->trans('layout.nav.admin_categories'),
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</section>

<?php if ($flash instanceof \Slim\Flash\Messages): ?>
    <?php foreach ($flash->getMessages() as $type => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <div class="alert alert-<?= $this->e($type === 'error' || $type === 'admin_error' ? 'danger' : 'success') ?> alert-dismissible fade show" role="alert">
                <?= $this->e($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= $this->e($this->trans('common.close')) ?>"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3"><?= $this->e($this->trans('admin.categories.table.title')) ?></h2>
                <?php if ($categories === []): ?>
                    <p class="text-muted mb-0"><?= $this->e($this->trans('admin.categories.table.empty')) ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                            <tr>
                                <th><?= $this->e($this->trans('admin.categories.table.name')) ?></th>
                                <th><?= $this->e($this->trans('admin.categories.table.parent')) ?></th>
                                <th class="text-end"><?= $this->e($this->trans('admin.categories.table.actions')) ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <strong><?= $this->e($category['name']) ?></strong>
                                    </td>
                                    <td>
                                        <?= $this->e($category['parent_name'] ?? '—') ?>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="<?= $this->e($this->locale_url('admin/categories', null, 'admin')) ?>?category=<?= $this->e($category['id']) ?>">
                                            <?= $this->e($this->trans('admin.categories.actions.edit')) ?>
                                        </a>
                                        <form method="post" class="d-inline-block ms-1" onsubmit="return confirm('<?= $this->e($this->trans('admin.categories.actions.delete')) ?>?');">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="category_id" value="<?= $this->e($category['id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <?= $this->e($this->trans('admin.categories.actions.delete')) ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">
                    <?= $this->e($this->trans($selectedId ? 'admin.categories.form.title_edit' : 'admin.categories.form.title_create')) ?>
                </h2>
                <form method="post">
                    <input type="hidden" name="action" value="save_category">
                    <?php if ($selectedId): ?>
                        <input type="hidden" name="category_id" value="<?= $this->e($selectedId) ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label text-muted small" for="category-name"><?= $this->e($this->trans('admin.categories.form.name')) ?></label>
                        <input class="form-control" id="category-name" type="text" name="name" value="<?= $this->e($selected['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small" for="category-parent"><?= $this->e($this->trans('admin.categories.form.parent')) ?></label>
                        <select class="form-select" id="category-parent" name="parent_id">
                            <option value=""><?= $this->e($this->trans('admin.categories.form.parent_placeholder')) ?></option>
                            <?php foreach ($parentOptions as $option): ?>
                                <option value="<?= $this->e($option['id']) ?>"<?= $selectedParentId === $option['id'] ? ' selected' : '' ?>>
                                    <?= $this->e($option['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <?= $this->e($this->trans($selectedId ? 'admin.categories.form.submit_update' : 'admin.categories.form.submit_create')) ?>
                    </button>
                </form>
            </div>
        </div>
        <?php if ($selectedId): ?>
            <div class="mt-3 text-center">
                <a class="text-muted small" href="<?= $this->e($this->locale_url('admin/categories', null, 'admin')) ?>">
                    <?= $this->e($this->trans('admin.categories.actions.create')) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
