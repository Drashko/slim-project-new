<?php
$this->layout('layout::public', [
    'title' => $this->trans('front.api.meta_title'),
]);
?>
<div class="alert alert-info" role="alert">
    <?= $this->e($this->trans('front.api.greeting')) ?>
</div>
