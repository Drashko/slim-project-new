<?php

declare(strict_types=1);

namespace App\Domain\Ad;

use App\Domain\User\User;
use App\Domain\User\UserInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'ads')]
class Ad implements AdInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(type: 'string', length: 180)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $images = [];

    #[ORM\Column(type: 'string', length: 120)]
    private string $category;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'Pending';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        User $owner,
        string $title,
        string $description,
        string $category,
        array $images = [],
        string $status = 'Pending'
    ) {
        $this->id = Uuid::v4()->toRfc4122();
        $this->owner = $owner;
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setCategory($category);
        $this->setImages($images);
        $this->setStatus($status);
        $this->createdAt = new DateTimeImmutable('now');
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwner(): UserInterface
    {
        return $this->owner;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $normalized = trim($title);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Title cannot be empty.');
        }

        $this->title = $normalized;
        $this->touch();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $normalized = trim($description);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Description cannot be empty.');
        }

        $this->description = $normalized;
        $this->touch();
    }

    /**
     * @return string[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param string[] $images
     */
    public function setImages(array $images): void
    {
        $normalized = [];
        foreach ($images as $image) {
            $candidate = trim((string) $image);
            if ($candidate !== '') {
                $normalized[] = $candidate;
            }
        }

        $this->images = array_values(array_unique($normalized));
        $this->touch();
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $normalized = trim($category);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Category is required.');
        }

        $this->category = $normalized;
        $this->touch();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $normalized = ucfirst(strtolower(trim($status)));
        if ($normalized === '') {
            throw new \InvalidArgumentException('Status cannot be empty.');
        }

        $this->status = $normalized;
        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable('now');
    }
}
