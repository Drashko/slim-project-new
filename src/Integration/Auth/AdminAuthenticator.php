<?php

declare(strict_types=1);

namespace App\Integration\Auth;

use App\Domain\Auth\Identity;
use App\Domain\Shared\DomainException;
use App\Integration\Rbac\Policy;
use Odan\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AdminAuthenticator
{
    private const ADMIN_ACCESS_PERMISSION = 'admin.access';

    public function __construct(
        private SessionInterface $session,
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
            if (!$this->policy->isGranted($identity->getRoles(), self::ADMIN_ACCESS_PERMISSION)) {
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

        $roles = array_map(static fn(mixed $role): string => (string) $role, $roles);

        return $this->policy->isGranted($roles, self::ADMIN_ACCESS_PERMISSION);
    }
}
