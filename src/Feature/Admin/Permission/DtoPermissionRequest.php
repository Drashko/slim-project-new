<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission;

final class DtoPermissionRequest
{
    private string $search;

    /**
     * @var string[]
     */
    private array $granted;

    /**
     * @param array<string, mixed> $query
     */
    public function __construct(array $query)
    {
        $this->search = trim((string) ($query['q'] ?? ''));

        $selected = $query['granted'] ?? [];
        $selected = is_array($selected) ? $selected : [];

        $normalized = [];
        foreach ($selected as $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        $this->granted = array_values(array_unique($normalized));
    }

    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @return string[]
     */
    public function getGranted(): array
    {
        return $this->granted;
    }
}
