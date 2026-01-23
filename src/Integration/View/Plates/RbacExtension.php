<?php

declare(strict_types=1);

namespace App\Integration\View\Plates;

use App\Domain\Auth\Identity;
use App\Integration\Rbac\Policy;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use App\Integration\Session\AdminSessionInterface;
use Stringable;

/**
 * Provides RBAC-aware helper functions to Plates templates.
 */
final readonly class RbacExtension implements ExtensionInterface
{
    public function __construct(
        private Policy           $policy,
        private AdminSessionInterface $session
    ) {
    }

    public function register(Engine $engine): void
    {
        $engine->registerFunction('can', function (string $ability, mixed $subject = null): bool {
            return $this->isGranted($ability, $subject);
        });

        $engine->registerFunction('cannot', function (string $ability, mixed $subject = null): bool {
            return !$this->isGranted($ability, $subject);
        });

        $engine->registerFunction('current_user_roles', function (): array {
            return $this->resolveRoles(null);
        });
    }

    private function isGranted(string $ability, mixed $subject): bool
    {
        $roles = $this->resolveRoles($subject);
        if ($roles === []) {
            return false;
        }

        return $this->policy->isGranted($roles, $ability);
    }

    /**
     * @return string[]
     */
    private function resolveRoles(mixed $subject): array
    {
        if ($subject === null) {
            $sessionUser = $this->session->get('user');
            if (is_array($sessionUser)) {
                $subject = $sessionUser;
            }
        }

        if ($subject instanceof Identity) {
            return $this->normalizeRoles($subject->getRoles());
        }

        if (is_array($subject)) {
            if (array_key_exists('roles', $subject)) {
                return $this->normalizeRoles($subject['roles']);
            }

            if ($this->isList($subject)) {
                return $this->normalizeRoles($subject);
            }

            return [];
        }

        return $this->normalizeRoles($subject);
    }

    /**
     * @return string[]
     */
    private function normalizeRoles(mixed $roles): array
    {
        if ($roles === null) {
            return [];
        }

        if ($roles instanceof Identity) {
            $roles = $roles->getRoles();
        }

        if ($roles instanceof Stringable) {
            $roles = [(string) $roles];
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $normalized = [];
        foreach ($roles as $role) {
            if ($role instanceof Identity) {
                $role = $role->getRoles();
                foreach ($this->normalizeRoles($role) as $nestedRole) {
                    $normalized[] = $nestedRole;
                }
                continue;
            }

            if ($role instanceof Stringable) {
                $role = (string) $role;
            } elseif (!is_scalar($role)) {
                continue;
            }

            $roleString = trim((string) $role);
            if ($roleString !== '') {
                $normalized[] = $roleString;
            }
        }

        return $normalized;
    }

    private function isList(array $values): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($values);
        }

        return $values === [] || array_keys($values) === range(0, count($values) - 1);
    }
}
