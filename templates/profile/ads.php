<?php
/** @var array|null $user */
/** @var array<int, array<string, mixed>> $ads */
/** @var string[] $categories */
/** @var \App\Integration\Flash\FlashMessages|null $flash */

use App\Integration\Flash\FlashMessages;

$this->layout('layout::home', [
    'title' => $this->trans('profile.ads.meta_title'),
    'user' => $user ?? null,
]);

$flashMessages = $flash instanceof FlashMessages ? $flash->getMessages() : [];
$ads = $ads ?? [];
$categories = $categories ?? [];
?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <header class="mb-4">
                    <p class="text-uppercase small text-secondary mb-1"><?= $this->e($this->trans('profile.ads.badge')) ?></p>
                    <h1 class="h4 mb-2"><?= $this->e($this->trans('profile.ads.heading')) ?></h1>
                    <p class="text-muted mb-0"><?= $this->e($this->trans('profile.ads.description')) ?></p>
                </header>

                <?php if ($flashMessages !== []): ?>
                    <?php foreach ($flashMessages as $type => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?= $this->e($type === 'error' ? 'danger' : 'success') ?> alert-dismissible fade show" role="alert">
                                <?= $this->e($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= $this->e($this->trans('common.close')) ?>"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="title"><?= $this->e($this->trans('profile.ads.fields.title')) ?></label>
                        <input class="form-control" type="text" id="title" name="title" required>
                        <div class="form-text"><?= $this->e($this->trans('profile.ads.help.title')) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="description"><?= $this->e($this->trans('profile.ads.fields.description')) ?></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                        <div class="form-text"><?= $this->e($this->trans('profile.ads.help.description')) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="category"><?= $this->e($this->trans('profile.ads.fields.category')) ?></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value=""><?= $this->e($this->trans('profile.ads.fields.category_placeholder')) ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $this->e($category) ?>"><?= $this->e($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label fw-semibold" for="images"><?= $this->e($this->trans('profile.ads.fields.images')) ?></label>
                        <input class="form-control" type="file" id="images" name="images[]" accept="image/*" multiple>
                        <div class="form-text"><?= $this->e($this->trans('profile.ads.help.images')) ?></div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-upload me-2" aria-hidden="true"></i>
                            <?= $this->e($this->trans('profile.ads.actions.publish')) ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="text-uppercase small text-secondary mb-1"><?= $this->e($this->trans('profile.ads.list.badge')) ?></p>
                        <h2 class="h5 mb-0"><?= $this->e($this->trans('profile.ads.list.heading')) ?></h2>
                    </div>
                    <span class="badge bg-light text-dark border">
                        <?= $this->e($this->trans('profile.ads.list.count', ['%count%' => (string) count($ads)])) ?>
                    </span>
                </div>

                <?php if ($ads === []): ?>
                    <div class="alert alert-info mb-0" role="alert">
                        <?= $this->e($this->trans('profile.ads.list.empty')) ?>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($ads as $ad): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <p class="small text-muted mb-1"><?= $this->e($ad['created_at'] ?? '') ?></p>
                                        <h3 class="h6 mb-1"><?= $this->e($ad['title'] ?? '') ?></h3>
                                        <p class="mb-2 text-muted small"><?= $this->e($ad['description'] ?? '') ?></p>
                                        <p class="mb-0 small">
                                            <i class="fa-solid fa-tags me-2" aria-hidden="true"></i>
                                            <?= $this->e($ad['category'] ?? '') ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
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
                                        <?php if (!empty($ad['images'])): ?>
                                            <div class="d-flex gap-2 flex-wrap justify-content-end mt-2">
                                                <?php foreach ($ad['images'] as $image): ?>
                                                    <img src="<?= $this->e($image) ?>" alt="<?= $this->e($ad['title'] ?? '') ?>" class="rounded border profile-ad-thumbnail">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
