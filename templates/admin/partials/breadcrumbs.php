<?php
/**
 * @var array<int, array{label: string, href?: string|null, active?: bool}> $items
 */

$items = array_values(array_filter($items ?? [], static function ($item): bool {
    return is_array($item ?? null) && ($item['label'] ?? '') !== '';
}));

if ($items === []) {
    return;
}

$lastIndex = count($items) - 1;
?>
<ol class="breadcrumb small mb-0 justify-content-lg-end">
    <?php foreach ($items as $index => $item): ?>
        <?php $isActive = $item['active'] ?? ($index === $lastIndex); ?>
        <li
            class="breadcrumb-item<?= $isActive ? ' active' : '' ?>"
            <?= $isActive ? 'aria-current="page"' : '' ?>
        >
            <?php if (!$isActive && !empty($item['href'])): ?>
                <a href="<?= $this->e((string) $item['href']) ?>">
                    <?= $this->e((string) $item['label']) ?>
                </a>
            <?php else: ?>
                <?= $this->e((string) $item['label']) ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ol>
