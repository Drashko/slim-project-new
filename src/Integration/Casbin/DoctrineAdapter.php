<?php

declare(strict_types=1);

namespace App\Integration\Casbin;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use ReflectionMethod;

final readonly class DoctrineAdapter implements Adapter
{
    public function __construct(private CasbinRuleRepository $repository)
    {
    }

    public function loadPolicy(Model $model): void
    {
        $loader = $this->resolvePolicyLineLoader();

        foreach ($this->repository->all() as $rule) {
            $loader($rule->toPolicyLine(), $model);
        }
    }

    /**
     * @return callable(string, Model):void
     */
    private function resolvePolicyLineLoader(): callable
    {
        $method = new ReflectionMethod(AdapterHelper::class, 'loadPolicyLine');

        if ($method->isStatic()) {
            return static function (string $line, Model $model): void {
                AdapterHelper::loadPolicyLine($line, $model);
            };
        }

        $helper = new class {
            use AdapterHelper;
        };

        return static function (string $line, Model $model) use ($helper): void {
            $helper->loadPolicyLine($line, $model);
        };
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
