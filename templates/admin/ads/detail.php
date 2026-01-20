<?php
/** @var array|null $user */
/** @var array<string, mixed> $ad */
/** @var string[] $statuses */
/** @var \Slim\Flash\Messages|null $flash */

use Slim\Flash\Messages;

$this->layout('layout::admin', [
    'title' => $this->trans('admin.ads.detail.meta_title', ['%title%' => $ad['title'] ?? 'Ad']),
    'user' => $user ?? null,
]);

$flashMessages = $flash instanceof Messages ? $flash->getMessages() : [];
$statuses = $statuses ?? ['Pending', 'Published', 'Archived'];
?>

<section class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-lg-8">
                <p class="text-uppercase text-muted small mb-1"><?= $this->e($this->trans('admin.ads.detail.badge')) ?></p>
                <h1 class="h3 mb-1"><?= $this->e($ad['title'] ?? '') ?></h1>
                <p class="mb-0 text-secondary"><?= $this->e($this->trans('admin.ads.detail.description')) ?></p>
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
                            'href' => $this->locale_url('admin/ads', null, 'admin'),
                        ],
                        [
                            'label' => $ad['title'] ?? 'Ad',
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

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i>
            <?= $this->e($this->trans('admin.ads.detail.form_title')) ?>
        </h3>
        <div class="card-tools text-muted small">
            <?= $this->e($this->trans('admin.ads.detail.last_updated', ['%date%' => $ad['updated_at'] ?? ''])) ?>
        </div>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-12">
                <label class="form-label text-muted small" for="title"><?= $this->e($this->trans('admin.ads.detail.fields.title')) ?></label>
                <input class="form-control" type="text" id="title" name="title" value="<?= $this->e($ad['title'] ?? '') ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small" for="description"><?= $this->e($this->trans('admin.ads.detail.fields.description')) ?></label>
                <textarea class="form-control" id="description" name="description" rows="6" required><?= $this->e($ad['description'] ?? '') ?></textarea>
                <div class="form-text"><?= $this->e($this->trans('admin.ads.detail.help.description')) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small" for="category"><?= $this->e($this->trans('admin.ads.detail.fields.category')) ?></label>
                <?php if (!empty($categories)): ?>
                    <select class="form-select" id="category" name="category" required>
                        <option value=""><?= $this->e($this->trans('profile.ads.fields.category_placeholder')) ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $this->e($category) ?>"<?= (string) ($ad['category'] ?? '') === $category ? ' selected' : '' ?>>
                                <?= $this->e($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input class="form-control" type="text" id="category" name="category" value="<?= $this->e($ad['category'] ?? '') ?>" required>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small" for="status"><?= $this->e($this->trans('admin.ads.detail.fields.status')) ?></label>
                <select class="form-select" id="status" name="status">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= $this->e($status) ?>"<?= strcasecmp((string) ($ad['status'] ?? ''), $status) === 0 ? ' selected' : '' ?>>
                            <?= $this->e($status) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><?= $this->e($this->trans('admin.ads.detail.help.status')) ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small" for="images"><?= $this->e($this->trans('admin.ads.detail.fields.images')) ?></label>
                <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple>
                <div class="form-text"><?= $this->e($this->trans('admin.ads.detail.help.images')) ?></div>
                <?php if (!empty($ad['images'])): ?>
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        <?php foreach ($ad['images'] as $image): ?>
                            <img src="<?= $this->e($image) ?>" alt="<?= $this->e($ad['title'] ?? '') ?>" class="rounded border admin-ad-thumbnail">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="text-muted small mb-0">
                        <?= $this->e($this->trans('admin.ads.detail.created_at', ['%date%' => $ad['created_at'] ?? ''])) ?>
                    </p>
                    <div class="d-grid d-lg-flex gap-2">
                        <button class="btn btn-outline-secondary" type="button" onclick="window.history.back()">
                            <?= $this->e($this->trans('admin.ads.detail.actions.back')) ?>
                        </button>
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-floppy-disk me-2" aria-hidden="true"></i>
                            <?= $this->e($this->trans('admin.ads.detail.actions.save')) ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
