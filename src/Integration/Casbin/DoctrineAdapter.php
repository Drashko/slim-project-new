<?php

declare(strict_types=1);

namespace App\Integration\Casbin;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;

final readonly class DoctrineAdapter implements Adapter
{
    public function __construct(private CasbinRuleRepository $repository)
    {
    }

    public function loadPolicy(Model $model): void
    {
        foreach ($this->repository->all() as $rule) {
            AdapterHelper::loadPolicyLine($rule->toPolicyLine(), $model);
        }
    }

    public function savePolicy(Model $model): void
    {
        $this->repository->clear();

        foreach ($model->getModel()['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->repository->add($ptype, $rule);
            }
        }

        foreach ($model->getModel()['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->repository->add($ptype, $rule);
            }
        }

        $this->repository->flush();
    }

    /**
     * @param array<int, string> $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->repository->add($ptype, $rule);
        $this->repository->flush();
    }

    /**
     * @param array<int, string> $rule
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $this->repository->remove($ptype, $rule);
        $this->repository->flush();
    }

    /**
     * @param array<int, string> $fieldValues
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, ...$fieldValues): void
    {
        $this->repository->removeFiltered($ptype, $fieldIndex, $fieldValues);
        $this->repository->flush();
    }
}
