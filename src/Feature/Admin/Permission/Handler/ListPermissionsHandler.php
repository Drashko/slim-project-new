<?php

declare(strict_types=1);

namespace App\Feature\Admin\Permission\Handler;

use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Feature\Admin\Permission\Command\ListPermissionsCommand;
use App\Feature\Admin\Permission\DtoPermission;
use App\Feature\Admin\Permission\DtoPermissionGroup;
use App\Feature\Admin\Permission\DtoPermissionRequest;
use App\Feature\Admin\Permission\PermissionMatrixResult;

final readonly class ListPermissionsHandler
{
    public function __construct(private PermissionRepositoryInterface $permissions)
    {
    }

    public function handle(ListPermissionsCommand $command): PermissionMatrixResult
    {
        $input = $command->getInput();
        $allPermissions = $this->permissions->all();
        $granted = $this->resolveGranted($input, $allPermissions);
        $groups = $this->filterGroups($input->getSearch(), $allPermissions);

        return new PermissionMatrixResult($groups, $granted, count($allPermissions));
    }

    /**
     * @param PermissionInterface[] $permissions
     * @return string[]
     */
    private function resolveGranted(DtoPermissionRequest $input, array $permissions): array
    {
        $selected = $input->getGranted();
        if ($selected === []) {
            return array_values(array_map(static fn(PermissionInterface $permission): string => $permission->getKey(), $permissions));
        }

        return $selected;
    }

    /**
     * @param PermissionInterface[] $permissions
     * @return DtoPermissionGroup[]
     */
    private function filterGroups(string $search, array $permissions): array
    {
        $grouped = [];
        foreach ($permissions as $permission) {
            if (!$permission instanceof PermissionInterface) {
                continue;
            }

            $key = $permission->getKey();
            [$groupKey] = array_pad(explode('.', $key, 2), 2, 'general');
            $grouped[$groupKey]['id'] = $groupKey;
            $grouped[$groupKey]['label'] = ucfirst($groupKey);
            $grouped[$groupKey]['description'] = $grouped[$groupKey]['description'] ?? '';
            $grouped[$groupKey]['permissions'][] = new DtoPermission($key, $permission->getLabel());
        }

        $groups = array_values(array_map(
            static fn(array $group): DtoPermissionGroup => new DtoPermissionGroup(
                (string) ($group['id'] ?? 'general'),
                (string) ($group['label'] ?? 'General'),
                (string) ($group['description'] ?? ''),
                array_values($group['permissions'] ?? [])
            ),
            $grouped
        ));

        if ($search === '') {
            return $groups;
        }

        $searchLower = strtolower($search);

        $filtered = [];
        foreach ($groups as $group) {
            $matches = array_values(array_filter(
                $group->getPermissions(),
                static fn(DtoPermission $permission): bool => str_contains(strtolower($permission->getLabel()), $searchLower)
            ));

            if ($matches === []) {
                continue;
            }

            $filtered[] = new DtoPermissionGroup(
                $group->getId(),
                $group->getLabel(),
                $group->getDescription(),
                $matches
            );
        }

        return $filtered;
    }
}
