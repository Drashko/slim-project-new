<?php
/** @var array|null $user */

$this->layout('layout::home', [
    'title' => $this->trans('profile.meta_title'),
    'user' => $user ?? null,
]);

$roles = array_map(static fn($role) => (string) $role, (array) ($user['roles'] ?? []));
$adsPreview = [
    [
        'title' => 'Vintage Desk Lamp',
        'status' => 'Active',
        'views' => 128,
        'price' => '$45',
        'category' => 'Home & Decor',
    ],
    [
        'title' => 'City Bike - 7 Speed',
        'status' => 'Paused',
        'views' => 54,
        'price' => '$210',
        'category' => 'Sports',
    ],
    [
        'title' => 'Freelance Logo Design',
        'status' => 'Draft',
        'views' => 12,
        'price' => '$120',
        'category' => 'Services',
    ],
];
$paymentMethods = [
    ['label' => 'Visa ending in 4021', 'status' => 'Primary'],
    ['label' => 'PayPal • alex@example.com', 'status' => 'Backup'],
];
?>
<div class="row g-4">
    <aside class="col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 sticky-lg-top" style="top: 1.5rem;">
            <div class="card-body p-4">
                <p class="text-uppercase small text-secondary mb-2">Profile</p>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h1 class="h6 mb-1"><?= $this->e($this->trans('profile.heading')) ?></h1>
                        <p class="text-muted small mb-0">Manage your public profile</p>
                    </div>
                </div>
                <?php if ($user !== null): ?>
                    <div class="d-flex align-items-center justify-content-between bg-body-tertiary rounded-3 px-3 py-2">
                        <div>
                            <p class="small text-muted mb-0">Signed in as</p>
                            <p class="small fw-semibold mb-0"><?= $this->e($user['email'] ?? '') ?></p>
                        </div>
                        <span class="badge text-bg-success">Active</span>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info small mb-0" role="alert">
                        <?= $this->e($this->trans('profile.empty_state')) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action" href="#general-info">
                    <i class="fa-solid fa-circle-info me-2 text-primary" aria-hidden="true"></i>General info
                </a>
                <a class="list-group-item list-group-item-action" href="#location">
                    <i class="fa-solid fa-location-dot me-2 text-primary" aria-hidden="true"></i>Location
                </a>
                <a class="list-group-item list-group-item-action" href="#password">
                    <i class="fa-solid fa-lock me-2 text-primary" aria-hidden="true"></i>Password change
                </a>
                <a class="list-group-item list-group-item-action" href="#ads">
                    <i class="fa-solid fa-bullhorn me-2 text-primary" aria-hidden="true"></i>Ads & listings
                </a>
                <a class="list-group-item list-group-item-action" href="#payments">
                    <i class="fa-solid fa-credit-card me-2 text-primary" aria-hidden="true"></i>Payments
                </a>
            </div>
        </div>
    </aside>
    <div class="col-lg-9">
        <div class="d-flex flex-column gap-4">
            <section id="general-info" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-uppercase small text-secondary mb-1">General info</p>
                            <h2 class="h4 mb-2"><?= $this->e($this->trans('profile.heading')) ?></h2>
                            <p class="text-muted mb-0"><?= $this->e($this->trans('profile.description')) ?></p>
                        </div>
                        <?php if ($user !== null): ?>
                            <a class="btn btn-outline-primary" href="<?= $this->e($this->locale_url('auth/logout')) ?>">
                                <i class="fa-solid fa-right-from-bracket me-1" aria-hidden="true"></i><?= $this->e($this->trans('profile.actions.logout')) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <hr class="my-4">
                    <?php if ($user !== null): ?>
                        <p class="text-success fw-semibold"><?= $this->e($this->trans('profile.signed_in', [
                                '%email%' => $user['email'] ?? '',
                            ])) ?></p>
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-secondary"><?= $this->e($this->trans('profile.session.email')) ?></dt>
                            <dd class="col-sm-8 mb-1"><?= $this->e($user['email'] ?? '') ?></dd>
                            <dt class="col-sm-4 text-secondary"><?= $this->e($this->trans('profile.session.roles')) ?></dt>
                            <dd class="col-sm-8 mb-0"><?= $this->e($roles === [] ? '—' : implode(', ', $roles)) ?></dd>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted mb-3"><?= $this->e($this->trans('profile.signed_out')) ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary" href="<?= $this->e($this->locale_url('profile/login')) ?>"><?= $this->e($this->trans('profile.actions.login')) ?></a>
                            <a class="btn btn-outline-primary" href="<?= $this->e($this->locale_url('profile/ads')) ?>"><?= $this->e($this->trans('profile.actions.ads')) ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section id="location" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-uppercase small text-secondary mb-1">Location</p>
                            <h3 class="h5 mb-2">Delivery and pickup zones</h3>
                            <p class="text-muted mb-0">Keep your location details fresh so buyers can discover nearby offers.</p>
                        </div>
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fa-solid fa-pen me-1" aria-hidden="true"></i>Update location
                        </button>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="small text-muted mb-1">Primary city</p>
                                <p class="fw-semibold mb-0">Sofia, Bulgaria</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="small text-muted mb-1">Pickup radius</p>
                                <p class="fw-semibold mb-0">Up to 15 km</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="password" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <p class="text-uppercase small text-secondary mb-1">Password change</p>
                    <h3 class="h5 mb-2">Secure your account</h3>
                    <p class="text-muted mb-4">Update your password regularly to keep your profile protected.</p>
                    <form class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="current-password">Current password</label>
                            <input class="form-control" id="current-password" type="password" placeholder="••••••••">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="new-password">New password</label>
                            <input class="form-control" id="new-password" type="password" placeholder="At least 8 characters">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="confirm-password">Confirm password</label>
                            <input class="form-control" id="confirm-password" type="password" placeholder="Repeat password">
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-primary" type="button">
                                <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>Save password
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section id="ads" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-uppercase small text-secondary mb-1">Ads & listings</p>
                            <h3 class="h5 mb-2">Manage your active campaigns</h3>
                            <p class="text-muted mb-0">Filter and review ads before you publish or boost them.</p>
                        </div>
                        <a class="btn btn-outline-primary" href="<?= $this->e($this->locale_url('profile/ads')) ?>">
                            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Create ad
                        </a>
                    </div>
                    <div class="bg-body-tertiary rounded-3 p-3 mt-4">
                        <form class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="ad-search">Search</label>
                                <input class="form-control" id="ad-search" type="search" placeholder="Search ads">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="ad-status">Status</label>
                                <select class="form-select" id="ad-status">
                                    <option selected>All statuses</option>
                                    <option>Active</option>
                                    <option>Paused</option>
                                    <option>Draft</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="ad-sort">Sort by</label>
                                <select class="form-select" id="ad-sort">
                                    <option selected>Most recent</option>
                                    <option>Highest views</option>
                                    <option>Price</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <?php foreach ($adsPreview as $ad): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <h4 class="h6 mb-1"><?= $this->e($ad['title']) ?></h4>
                                        <p class="small text-muted mb-0"><?= $this->e($ad['category']) ?> • <?= $this->e($ad['views']) ?> views</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge text-bg-light border text-uppercase">
                                            <?= $this->e($ad['status']) ?>
                                        </span>
                                        <span class="fw-semibold"><?= $this->e($ad['price']) ?></span>
                                        <button class="btn btn-sm btn-outline-secondary" type="button">Edit</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section id="payments" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-uppercase small text-secondary mb-1">Payments</p>
                            <h3 class="h5 mb-2">Billing preferences</h3>
                            <p class="text-muted mb-0">Connect payout methods and review upcoming charges.</p>
                        </div>
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add method
                        </button>
                    </div>
                    <div class="mt-4">
                        <div class="row g-3">
                            <?php foreach ($paymentMethods as $method): ?>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="fw-semibold mb-1"><?= $this->e($method['label']) ?></p>
                                            <p class="small text-muted mb-0"><?= $this->e($method['status']) ?></p>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary" type="button">Manage</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="alert alert-light border mt-4 mb-0" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check text-success" aria-hidden="true"></i>
                                <div>
                                    <p class="fw-semibold mb-1">Next payout scheduled</p>
                                    <p class="small text-muted mb-0">Friday, 15 September • Estimated $320</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
