<?php

declare(strict_types=1);

namespace App\Integration\Auth;

use App\Domain\Auth\Identity;
use App\Domain\Shared\DomainException;
use App\Integration\Rbac\Policy;
use App\Integration\Session\AdminSessionInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AdminAuthenticator
{
    private const ADMIN_ACCESS_PERMISSION = 'admin.access';

    public function __construct(
        private AdminSessionInterface $session,
        private Policy $policy
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function authenticate(ServerRequestInterface $request): array
    {
        $identity = $request->getAttribute(Identity::class);
        if ($identity instanceof Identity) {
            $roles = $this->normalizeRoles($identity->getRoles());
            if (!$this->hasAdminRole($roles) && !$this->policy->isGranted($roles, self::ADMIN_ACCESS_PERMISSION)) {
                throw new DomainException('Admin privileges are required.');
            }

            return $identity->toArray();
        }

        $user = $this->session->get('user');
        if (is_array($user) && $this->userCanAccessAdmin($user)) {
            return $user;
        }

        throw new DomainException('You must be signed in as an administrator.');
    }

    /**
     * @param array<string, mixed> $user
     */
    private function userCanAccessAdmin(array $user): bool
    {
        $roles = $user['roles'] ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = $this->normalizeRoles($roles);

        return $this->hasAdminRole($roles) || $this->policy->isGranted($roles, self::ADMIN_ACCESS_PERMISSION);
    }

    /**
     * @param string[] $roles
     */
    private function hasAdminRole(array $roles): bool
    {
        return in_array('ROLE_ADMIN', $roles, true);
    }

    /**
     * @param array<int, mixed> $roles
     *
     * @return string[]
     */
    private function normalizeRoles(array $roles): array
    {
        $normalized = [];
        foreach ($roles as $role) {
            if (!is_scalar($role)) {
                continue;
            }

            $value = strtoupper(trim((string) $role));
            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }
}
