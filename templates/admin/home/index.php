<?php
/** @var array|null $user */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.dashboard.meta_title'),
    'user' => $user ?? null,
]);
?>
<div class="py-5 text-center">
    <h1 class="h3 mb-3"><?= $this->e($this->trans('admin.home.heading')) ?></h1>
    <p class="text-muted mb-0"><?= $this->e($this->trans('admin.home.subheading')) ?></p>
</div>
