<?php

declare(strict_types=1);

namespace Tests\Integration\Rbac;

use App\Integration\Rbac\Policy;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\Role as LaminasRole;
use PHPUnit\Framework\TestCase;

final class PolicyTest extends TestCase
{
    public function testIsGrantedReturnsTrueWhenAnyRoleHasPermission(): void
    {
        $rbac = new Rbac();
        $admin = new LaminasRole('ROLE_ADMIN');
        $admin->addPermission('admin.access');
        $rbac->addRole($admin);

        $policy = new Policy($rbac);

        self::assertTrue($policy->isGranted([' role_user ', 'ROLE_ADMIN'], ' ADMIN.ACCESS '));
    }

    public function testIsGrantedReturnsFalseWhenNoRolesOrAbilityProvided(): void
    {
        $rbac = new Rbac();
        $policy = new Policy($rbac);

        self::assertFalse($policy->isGranted([], ''));
        self::assertFalse($policy->isGranted([''], 'admin.access'));
    }
}
