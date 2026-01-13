<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission;

final class PermissionMatrixResult
{
    /**
     * @param DtoPermissionGroup[] $groups
     * @param string[] $granted
     */
    public function __construct(
        private array $groups,
        private array $granted,
        private int $totalPermissions
    ) {
    }

    /**
     * @return DtoPermissionGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return string[]
     */
    public function getGranted(): array
    {
        return $this->granted;
    }

    public function getTotalPermissions(): int
    {
        return $this->totalPermissions;
    }
}
