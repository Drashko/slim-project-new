<?php

declare(strict_types=1);

namespace App\Web\Shared;

final class Paginator
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{items: array<int, array<string, mixed>>, page: int, perPage: int, total: int, totalPages: int, hasPrev: bool, hasNext: bool, from: int, to: int}
     */
    public function paginate(array $items, int $page, int $perPage): array
    {
        $total = count($items);
        $perPage = max(1, $perPage);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);
        $from = $total === 0 ? 0 : $offset + 1;
        $to = $total === 0 ? 0 : min($offset + $perPage, $total);

        return [
            'items' => $pageItems,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
            'from' => $from,
            'to' => $to,
        ];
    }
}
