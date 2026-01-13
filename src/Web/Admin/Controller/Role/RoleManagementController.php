<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Role;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\ListPermissionsCommand;
use App\Feature\Admin\Permission\DtoPermissionRequest;
use App\Feature\Admin\Permission\Handler\ListPermissionsHandler;
use App\Feature\Admin\Role\Command\CreateRoleCommand;
use App\Feature\Admin\Role\Command\DeleteRoleCommand;
use App\Feature\Admin\Role\Command\ListRolesCommand;
use App\Feature\Admin\Role\Command\ResolveSelectedRoleCommand;
use App\Feature\Admin\Role\Command\UpdateRolePermissionsCommand;
use App\Feature\Admin\Role\Handler\CreateRoleHandler;
use App\Feature\Admin\Role\Handler\DeleteRoleHandler;
use App\Feature\Admin\Role\Handler\ListRolesHandler;
use App\Feature\Admin\Role\Handler\ResolveSelectedRoleHandler;
use App\Feature\Admin\Role\Handler\UpdateRolePermissionsHandler;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RoleManagementController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer $templates,
        private AdminAuthenticator $authenticator,
        private ListRolesHandler $listRoles,
        private ResolveSelectedRoleHandler $resolveSelectedRole,
        private ListPermissionsHandler $listPermissions,
        private CreateRoleHandler $createRole,
        private DeleteRoleHandler $deleteRole,
        private UpdateRolePermissionsHandler $updatePermissions,
        private Messages $flash,
        private TranslatorInterface $translator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/login'))
                ->withStatus(302);
        }

        $roleDtoMap = $this->mapRoles();
        $selectedId = $this->resolveSelectedRole(
            $request,
            array_keys($roleDtoMap)
        );
        $selectedRole = $roleDtoMap[$selectedId] ?? [
            'id' => '',
            'name' => '',
            'description' => '',
            'members' => 0,
            'permissions' => [],
            'permissionKeys' => [],
            'critical' => false,
        ];

        $permissionMatrix = $this->listPermissions->handle(
            new ListPermissionsCommand(new DtoPermissionRequest([
                'granted' => $selectedRole['permissionKeys'] ?? [],
            ]))
        );

        if ($request->getMethod() === 'POST') {
            $body = (array) ($request->getParsedBody() ?? []);
            $action = (string) ($body['action'] ?? '');

            try {
                if ($action === 'create') {
                    $created = $this->createRole->handle(new CreateRoleCommand(
                        (string) ($body['role_key'] ?? ''),
                        (string) ($body['name'] ?? ''),
                        (string) ($body['description'] ?? ''),
                        $this->extractPermissions($body),
                        isset($body['critical'])
                    ));

                    $this->flash->addMessage('success', $this->translator->trans('admin.roles.flash.created'));

                    return $response
                        ->withHeader('Location', $this->localizedPath($request, 'admin/roles') . '?role=' . urlencode($created->getKey()))
                        ->withStatus(302);
                }

                if ($action === 'delete') {
                    $roleKey = (string) ($body['role'] ?? $selectedId);

                    $this->deleteRole->handle(new DeleteRoleCommand($roleKey));

                    $this->flash->addMessage('success', $this->translator->trans('admin.roles.flash.deleted'));

                    return $response
                        ->withHeader('Location', $this->localizedPath($request, 'admin/roles'))
                        ->withStatus(302);
                }

                if ($action === 'update_permissions') {
                    $roleKey = (string) ($body['role'] ?? $selectedId);

                    $this->updatePermissions->handle(new UpdateRolePermissionsCommand(
                        $roleKey,
                        $this->extractPermissions($body)
                    ));

                    $this->flash->addMessage('success', $this->translator->trans('admin.roles.flash.permissions_updated'));

                    return $response
                        ->withHeader('Location', $this->localizedPath($request, 'admin/roles') . '?role=' . urlencode($roleKey))
                        ->withStatus(302);
                }
            } catch (DomainException $exception) {
                $this->flash->addMessage('error', $this->translator->trans($exception->getMessage()));
            }
        }

        return $this->templates->render($response, 'admin::roles/index', [
            'user' => $user,
            'roles' => array_values($roleDtoMap),
            'selectedRole' => $selectedRole,
            'selectedId' => $selectedId,
            'permissionGroups' => array_map(static fn($group) => $group->toArray(), $permissionMatrix->getGroups()),
            'selectedPermissions' => $selectedRole['permissionKeys'] ?? [],
            'allPermissions' => $this->flattenPermissions($permissionMatrix->getGroups()),
            'flash' => $this->flash,
        ]);
    }

    private function resolveSelectedRole(ServerRequestInterface $request, array $available): string
    {
        $params = $request->getQueryParams();
        $requested = (string) ($params['role'] ?? '');

        return $this->resolveSelectedRole->handle(new ResolveSelectedRoleCommand($requested, $available));
    }

    /**
     * @param array<string, mixed> $input
     * @return string[]
     */
    private function extractPermissions(array $input): array
    {
        $selected = $input['permissions'] ?? [];
        if (!is_array($selected)) {
            return [];
        }

        $normalized = [];
        foreach ($selected as $permission) {
            $permission = trim((string) $permission);
            if ($permission !== '') {
                $normalized[] = $permission;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function mapRoles(): array
    {
        $map = [];

        foreach ($this->listRoles->handle(new ListRolesCommand()) as $role) {
            $map[$role->getId()] = $role->toArray();
        }

        return $map;
    }

    /**
     * @param array<int, object> $groups
     * @return array<int, array<string, string>>
     */
    private function flattenPermissions(array $groups): array
    {
        $options = [];

        foreach ($groups as $group) {
            if (!method_exists($group, 'getPermissions')) {
                continue;
            }

            foreach ($group->getPermissions() as $permission) {
                if (!method_exists($permission, 'getKey')) {
                    continue;
                }

                $options[] = [
                    'key' => $permission->getKey(),
                    'label' => method_exists($permission, 'getLabel') ? $permission->getLabel() : $permission->getKey(),
                ];
            }
        }

        return $options;
    }
}
