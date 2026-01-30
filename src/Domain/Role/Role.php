<?php

declare(strict_types=1);

namespace App\Domain\Role;

use App\Domain\Permission\Permission;
use App\Domain\Permission\PermissionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

class Role implements RoleInterface
{
    private string $id;

    private string $key;

    private string $name;

    private string $description;

    private bool $critical;

    private int $memberCount;

    /**
     * @var Collection<int, PermissionInterface>
     */
    private Collection $permissions;

    public function __construct(string $key, string $name, string $description = '', bool $critical = false, int $memberCount = 0)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->key = strtolower(trim($key));
        $this->setName($name);
        $this->setDescription($description);
        $this->markCritical($critical);
        $this->setMemberCount($memberCount);
        $this->permissions = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Role name cannot be empty.');
        }

        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = trim($description);
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function markCritical(bool $critical = true): void
    {
        $this->critical = $critical;
    }

    public function getMemberCount(): int
    {
        return $this->memberCount;
    }

    public function setMemberCount(int $memberCount): void
    {
        $this->memberCount = max(0, $memberCount);
    }

    public function getPermissions(): array
    {
        return $this->permissions->toArray();
    }

    /**
     * @param PermissionInterface[] $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions->clear();

        foreach ($permissions as $permission) {
            if ($permission instanceof PermissionInterface) {
                $this->addPermission($permission);
            }
        }
    }

    public function addPermission(PermissionInterface $permission): void
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }
    }

    public function removePermission(PermissionInterface $permission): void
    {
        $this->permissions->removeElement($permission);
    }
}
