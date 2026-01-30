<?php
/** @var array|null $user */

$this->layout('layout::default', [
    'title' => $this->trans('home.home.meta_title'),
    'user' => $user ?? null,
]);
?>
<?= $this->react_mount('home-react-root', [], [
    'component' => 'App',
    'class' => 'react-home-root',
]) ?>
