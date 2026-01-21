<?php
/** @var array|null $user */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.dashboard.meta_title'),
    'user' => $user ?? null,
]);

$dashboardCopy = [
    'overview' => [
        'loading' => $this->trans('admin.dashboard.react.overview.loading'),
        'error' => $this->trans('admin.dashboard.react.overview.error'),
        'liveLabel' => $this->trans('admin.dashboard.react.overview.live_label'),
        'cards' => [
            [
                'title' => $this->trans('admin.dashboard.react.overview.cards.users.title'),
                'description' => $this->trans('admin.dashboard.react.overview.cards.users.description'),
                'icon' => 'fa-solid fa-user',
                'link' => 'users',
                'linkLabel' => $this->trans('admin.dashboard.react.overview.cards.users.link'),
            ],
            [
                'title' => $this->trans('admin.dashboard.react.overview.cards.roles.title'),
                'description' => $this->trans('admin.dashboard.react.overview.cards.roles.description'),
                'icon' => 'fa-solid fa-id-badge',
                'link' => 'roles',
                'linkLabel' => $this->trans('admin.dashboard.react.overview.cards.roles.link'),
            ],
            [
                'title' => $this->trans('admin.dashboard.react.overview.cards.ads.title'),
                'description' => $this->trans('admin.dashboard.react.overview.cards.ads.description'),
                'icon' => 'fa-solid fa-bullhorn',
                'link' => 'ads',
                'linkLabel' => $this->trans('admin.dashboard.react.overview.cards.ads.link'),
            ],
        ],
    ],
];
?>
<?= $this->react_mount('admin-dashboard-react-root', [
    'copy' => $dashboardCopy,
    'showOnlyOverview' => true,
], [
    'component' => 'AdminDashboard',
    'class' => 'react-admin-dashboard',
    'bundle' => 'admin',
    'attributes' => [
        'data-locale' => $this->current_locale(),
    ],
]) ?>
