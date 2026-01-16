<?php
/** @var array{page: int, perPage: int, total: int, totalPages: int, hasPrev: bool, hasNext: bool, from: int, to: int} $pagination */
/** @var string $baseUrl */
/** @var array<string, mixed> $queryParams */

$pagination = $pagination ?? [
    'page' => 1,
    'perPage' => 10,
    'total' => 0,
    'totalPages' => 1,
    'hasPrev' => false,
    'hasNext' => false,
    'from' => 0,
    'to' => 0,
];
$baseUrl = $baseUrl ?? '';
$queryParams = $queryParams ?? [];
$page = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['totalPages'] ?? 1);
$baseQueryParams = array_filter(
    $queryParams,
    static fn(mixed $value, string $key): bool => $key !== 'page' && $value !== '' && $value !== null,
    ARRAY_FILTER_USE_BOTH
);

$buildUrl = static function (string $url, array $params): string {
    $query = http_build_query($params);

    return $query === '' ? $url : $url . '?' . $query;
};
?>

<div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mt-3">
    <div class="text-muted small">
        <?= $this->e($this->trans('admin.users.pagination.summary', [
            '%from%' => (string) ($pagination['from'] ?? 0),
            '%to%' => (string) ($pagination['to'] ?? 0),
            '%total%' => (string) ($pagination['total'] ?? 0),
        ])) ?>
    </div>
    <nav aria-label="<?= $this->e($this->trans('admin.users.pagination.label')) ?>">
        <ul class="pagination mb-0">
            <li class="page-item<?= ($pagination['hasPrev'] ?? false) ? '' : ' disabled' ?>">
                <a
                    class="page-link"
                    href="<?= $this->e($buildUrl($baseUrl, array_merge($baseQueryParams, ['page' => max(1, $page - 1)]))) ?>"
                    aria-label="<?= $this->e($this->trans('admin.users.pagination.prev')) ?>"
                >
                    <?= $this->e($this->trans('admin.users.pagination.prev')) ?>
                </a>
            </li>
            <?php for ($index = 1; $index <= $totalPages; $index++): ?>
                <li class="page-item<?= $index === $page ? ' active' : '' ?>">
                    <a
                        class="page-link"
                        href="<?= $this->e($buildUrl($baseUrl, array_merge($baseQueryParams, ['page' => $index]))) ?>"
                    >
                        <?= $this->e((string) $index) ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item<?= ($pagination['hasNext'] ?? false) ? '' : ' disabled' ?>">
                <a
                    class="page-link"
                    href="<?= $this->e($buildUrl($baseUrl, array_merge($baseQueryParams, ['page' => min($totalPages, $page + 1)]))) ?>"
                    aria-label="<?= $this->e($this->trans('admin.users.pagination.next')) ?>"
                >
                    <?= $this->e($this->trans('admin.users.pagination.next')) ?>
                </a>
            </li>
        </ul>
    </nav>
</div>
