<?php
/** @var array|null $user */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.dashboard.meta_title'),
    'user' => $user ?? null,
]);
?>
<?= $this->react_mount('admin-dashboard-react-root', [], [
    'component' => 'AdminDashboard',
    'class' => 'react-admin-dashboard',
    'bundle' => 'admin',
]) ?>