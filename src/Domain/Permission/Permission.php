<?php

declare(strict_types=1);

namespace App\Domain\Permission;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'permissions')]
class Permission implements PermissionInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(name: 'permission_key', type: 'string', length: 120, unique: true)]
    private string $key;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    public function __construct(string $key, string $label)
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->key = strtolower(trim($key));
        $this->setLabel($label);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $label = trim($label);
        if ($label === '') {
            $label = $this->key;
        }

        $this->label = $label;
    }
}
