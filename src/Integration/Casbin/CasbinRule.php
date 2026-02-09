<?php

declare(strict_types=1);

namespace App\Integration\Casbin;

final class CasbinRule
{
    private int $id;
    private string $ptype;
    private ?string $v0 = null;
    private ?string $v1 = null;
    private ?string $v2 = null;
    private ?string $v3 = null;
    private ?string $v4 = null;
    private ?string $v5 = null;

    private function __construct(string $ptype)
    {
        $this->ptype = $ptype;
    }

    /**
     * @param array<int, string> $values
     */
    public static function fromPolicy(string $ptype, array $values): self
    {
        $rule = new self($ptype);
        $rule->v0 = $values[0] ?? null;
        $rule->v1 = $values[1] ?? null;
        $rule->v2 = $values[2] ?? null;
        $rule->v3 = $values[3] ?? null;
        $rule->v4 = $values[4] ?? null;
        $rule->v5 = $values[5] ?? null;

        return $rule;
    }

    public function getPtype(): string
    {
        return $this->ptype;
    }

    /**
     * @return array<int, string>
     */
    public function getValues(): array
    {
        $values = [$this->v0, $this->v1, $this->v2, $this->v3, $this->v4, $this->v5];

        return array_values(array_filter($values, static fn(?string $value): bool => $value !== null && $value !== ''));
    }

    public function toPolicyLine(): string
    {
        $values = $this->getValues();
        if ($values === []) {
            return $this->ptype;
        }

        return $this->ptype . ', ' . implode(', ', $values);
    }

    public function matchesPolicy(string $ptype, array $values): bool
    {
        if ($this->ptype !== $ptype) {
            return false;
        }

        return [
            $this->v0,
            $this->v1,
            $this->v2,
            $this->v3,
            $this->v4,
            $this->v5,
        ] === [
            $values[0] ?? null,
            $values[1] ?? null,
            $values[2] ?? null,
            $values[3] ?? null,
            $values[4] ?? null,
            $values[5] ?? null,
        ];
    }
}
