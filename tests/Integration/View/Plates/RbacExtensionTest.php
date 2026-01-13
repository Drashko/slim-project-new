<?php

declare(strict_types=1);

namespace Tests\Integration\View\Plates;

use App\Integration\Rbac\Policy;
use App\Integration\View\Plates\RbacExtension;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Permissions\Rbac\Role as LaminasRole;
use League\Plates\Engine;
use Odan\Session\SessionInterface;
use PHPUnit\Framework\TestCase;

final class RbacExtensionTest extends TestCase
{
    public function testCanUsesSessionUserByDefault(): void
    {
        $rbac = new Rbac();
        $admin = new LaminasRole('ROLE_ADMIN');
        $admin->addPermission('admin.access');
        $rbac->addRole($admin);

        $policy = new Policy($rbac);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('user')->willReturn(['roles' => ['ROLE_ADMIN']]);

        $engine = new Engine(__DIR__ . '/Fixtures');
        (new RbacExtension($policy, $session))->register($engine);

        self::assertSame('granted', trim($engine->render('can')));
    }

    public function testCannotHonorsExplicitUserRoles(): void
    {
        $rbac = new Rbac();
        $admin = new LaminasRole('ROLE_ADMIN');
        $admin->addPermission('admin.users.manage');
        $rbac->addRole($admin);

        $policy = new Policy($rbac);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('user')->willReturn(null);

        $engine = new Engine(__DIR__ . '/Fixtures');
        (new RbacExtension($policy, $session))->register($engine);

        $output = trim($engine->render('cannot', [
            'user' => [
                'roles' => ['ROLE_USER'],
            ],
        ]));

        self::assertSame('denied', $output);
    }

    public function testAdminAccessUsesCanHelper(): void
    {
        $rbac = new Rbac();
        $admin = new LaminasRole('ROLE_ADMIN');
        $admin->addPermission('admin.access');
        $rbac->addRole($admin);

        $policy = new Policy($rbac);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('user')->willReturn(['roles' => ['ROLE_ADMIN']]);

        $engine = new Engine(__DIR__ . '/Fixtures');
        (new RbacExtension($policy, $session))->register($engine);

        self::assertSame('granted', trim($engine->render('admin_access')));
    }

    public function testCanHidesAndShowsAdminSectionsByAbility(): void
    {
        $rbac = new Rbac();
        $admin = new LaminasRole('ROLE_ADMIN');
        $admin->addPermission('admin.users.view');
        $rbac->addRole($admin);

        $policy = new Policy($rbac);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->with('user')->willReturn(['roles' => ['ROLE_ADMIN']]);

        $engine = new Engine(__DIR__ . '/Fixtures');
        (new RbacExtension($policy, $session))->register($engine);

        self::assertSame('visible', trim($engine->render('users_view')));
    }
}
