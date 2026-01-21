<?php
/** @var array|null $user */

$this->layout('layout::admin', [
    'title' => $this->trans('admin.dashboard.meta_title'),
    'user' => $user ?? null,
]);

$dashboardCopy = [
    'hero' => [
        'badge' => $this->trans('admin.dashboard.react.hero.badge'),
        'title' => $this->trans('admin.dashboard.react.hero.title'),
        'lead' => $this->trans('admin.dashboard.react.hero.lead'),
        'actions' => [
            'primary' => $this->trans('admin.dashboard.react.hero.actions.primary'),
            'secondary' => $this->trans('admin.dashboard.react.hero.actions.secondary'),
        ],
    ],
    'today' => [
        'title' => $this->trans('admin.dashboard.react.today.title'),
        'items' => [
            [
                'title' => $this->trans('admin.dashboard.react.today.items.leads.title'),
                'detail' => $this->trans('admin.dashboard.react.today.items.leads.detail'),
                'value' => $this->trans('admin.dashboard.react.today.items.leads.value'),
                'valueClass' => 'text-primary',
            ],
            [
                'title' => $this->trans('admin.dashboard.react.today.items.ctr.title'),
                'detail' => $this->trans('admin.dashboard.react.today.items.ctr.detail'),
                'value' => $this->trans('admin.dashboard.react.today.items.ctr.value'),
                'valueClass' => 'text-success',
            ],
            [
                'title' => $this->trans('admin.dashboard.react.today.items.channel.title'),
                'detail' => $this->trans('admin.dashboard.react.today.items.channel.detail'),
                'value' => $this->trans('admin.dashboard.react.today.items.channel.value'),
                'valueClass' => 'text-dark',
            ],
        ],
    ],
    'highlights' => [
        [
            'title' => $this->trans('admin.dashboard.react.highlights.items.reach.title'),
            'value' => $this->trans('admin.dashboard.react.highlights.items.reach.value'),
            'detail' => $this->trans('admin.dashboard.react.highlights.items.reach.detail'),
        ],
        [
            'title' => $this->trans('admin.dashboard.react.highlights.items.avg_time.title'),
            'value' => $this->trans('admin.dashboard.react.highlights.items.avg_time.value'),
            'detail' => $this->trans('admin.dashboard.react.highlights.items.avg_time.detail'),
        ],
        [
            'title' => $this->trans('admin.dashboard.react.highlights.items.verified.title'),
            'value' => $this->trans('admin.dashboard.react.highlights.items.verified.value'),
            'detail' => $this->trans('admin.dashboard.react.highlights.items.verified.detail'),
        ],
    ],
    'feature' => [
        'title' => $this->trans('admin.dashboard.react.feature.title'),
        'description' => $this->trans('admin.dashboard.react.feature.description'),
        'cta' => $this->trans('admin.dashboard.react.feature.cta'),
        'cards' => [
            [
                'title' => $this->trans('admin.dashboard.react.feature.cards.targeting.title'),
                'description' => $this->trans('admin.dashboard.react.feature.cards.targeting.description'),
                'icon' => '🎯',
            ],
            [
                'title' => $this->trans('admin.dashboard.react.feature.cards.studio.title'),
                'description' => $this->trans('admin.dashboard.react.feature.cards.studio.description'),
                'icon' => '🎨',
            ],
            [
                'title' => $this->trans('admin.dashboard.react.feature.cards.performance.title'),
                'description' => $this->trans('admin.dashboard.react.feature.cards.performance.description'),
                'icon' => '📊',
            ],
            [
                'title' => $this->trans('admin.dashboard.react.feature.cards.budget.title'),
                'description' => $this->trans('admin.dashboard.react.feature.cards.budget.description'),
                'icon' => '🛡️',
            ],
        ],
    ],
    'categories' => [
        'title' => $this->trans('admin.dashboard.react.categories.title'),
        'description' => $this->trans('admin.dashboard.react.categories.description'),
        'items' => [
            $this->trans('admin.dashboard.react.categories.items.real_estate'),
            $this->trans('admin.dashboard.react.categories.items.auto'),
            $this->trans('admin.dashboard.react.categories.items.jobs'),
            $this->trans('admin.dashboard.react.categories.items.services'),
            $this->trans('admin.dashboard.react.categories.items.electronics'),
            $this->trans('admin.dashboard.react.categories.items.home'),
            $this->trans('admin.dashboard.react.categories.items.events'),
            $this->trans('admin.dashboard.react.categories.items.retail'),
        ],
    ],
    'quickActions' => [
        'items' => [
            [
                'title' => $this->trans('admin.dashboard.react.quick_actions.items.listing.title'),
                'detail' => $this->trans('admin.dashboard.react.quick_actions.items.listing.detail'),
                'action' => $this->trans('admin.dashboard.react.quick_actions.items.listing.action'),
            ],
            [
                'title' => $this->trans('admin.dashboard.react.quick_actions.items.bundle.title'),
                'detail' => $this->trans('admin.dashboard.react.quick_actions.items.bundle.detail'),
                'action' => $this->trans('admin.dashboard.react.quick_actions.items.bundle.action'),
            ],
            [
                'title' => $this->trans('admin.dashboard.react.quick_actions.items.invite.title'),
                'detail' => $this->trans('admin.dashboard.react.quick_actions.items.invite.detail'),
                'action' => $this->trans('admin.dashboard.react.quick_actions.items.invite.action'),
            ],
        ],
    ],
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
], [
    'component' => 'AdminDashboard',
    'class' => 'react-admin-dashboard',
    'bundle' => 'admin',
    'attributes' => [
        'data-locale' => $this->current_locale(),
    ],
]) ?>
