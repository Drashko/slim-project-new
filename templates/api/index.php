<?php
/** @var array|null $user */

$this->layout('layout::default', [
    'title' => $this->trans('front.api.meta_title'),
    'user' => $user ?? null,
]);
?>
<div class="alert alert-info" role="alert">
    <?= $this->e($this->trans('front.api.greeting')) ?>
</div>
