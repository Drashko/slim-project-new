<?php
/** @var array|null $user */

$this->layout('layout::public', [
    'title' => $this->trans('front.home.meta_title'),
    'user' => $user ?? null,
]);

?>
<?= $this->react_mount('home-react-root', [
    'user' => $user,
], [
    'component' => 'App',
    'class' => 'react-home-root',
]) ?>

