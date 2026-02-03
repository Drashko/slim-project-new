<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\ListPermissionsCommand;
use App\Feature\Admin\Permission\DtoPermissionRequest;
use App\Feature\Admin\Permission\Handler\ListPermissionsHandler;
use App\Feature\Admin\Role\Command\DeleteRoleCommand;
use App\Feature\Admin\Role\Command\ListRolesCommand;
use App\Feature\Admin\Role\Command\ResolveSelectedRoleCommand;
use App\Feature\Admin\Role\Command\UpdateRolePermissionsCommand;
use App\Feature\Admin\Role\Handler\DeleteRoleHandler;
use App\Feature\Admin\Role\Handler\ListRolesHandler;
use App\Feature\Admin\Role\Handler\ResolveSelectedRoleHandler;
use App\Feature\Admin\Role\Handler\UpdateRolePermissionsHandler;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RoleManagementController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private AdminAuthenticator $authenticator,
        private ListRolesHandler $listRoles,
        private ResolveSelectedRoleHandler $resolveSelectedRole,
        private ListPermissionsHandler $listPermissions,
        private DeleteRoleHandler $deleteRole,
        private UpdateRolePermissionsHandler $updatePermissions,
        private FlashMessages $flash,
        private TranslatorInterface $translator
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $this->respondWithJson($response, [
                'error' => 'Unauthorized',
                'redirect' => $this->localizedPath($request, 'admin/login'),
            ], 401);
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

        $statusCode = 200;

        if ($request->getMethod() === 'POST') {
            $body = (array) ($request->getParsedBody() ?? []);
            $action = (string) ($body['action'] ?? '');

            try {
                if ($action === 'delete') {
                    $roleKey = (string) ($body['role'] ?? $selectedId);

                    $this->deleteRole->handle(new DeleteRoleCommand($roleKey));

                    $this->flash->addMessage('success', $this->translator->trans('admin.roles.flash.deleted'));

                    return $this->respondWithJson($response, [
                        'status' => 'deleted',
                        'redirect' => $this->localizedPath($request, 'admin/roles'),
                        'messages' => $this->flash->getMessages(),
                    ]);
                }

                if ($action === 'update_permissions') {
                    $roleKey = (string) ($body['role'] ?? $selectedId);

                    $this->updatePermissions->handle(new UpdateRolePermissionsCommand(
                        $roleKey,
                        $this->extractPermissions($body)
                    ));

                    $this->flash->addMessage('success', $this->translator->trans('admin.roles.flash.permissions_updated'));

                    return $this->respondWithJson($response, [
                        'status' => 'updated',
                        'redirect' => $this->localizedPath($request, 'admin/roles') . '?role=' . urlencode($roleKey),
                        'messages' => $this->flash->getMessages(),
                    ]);
                }
            } catch (DomainException $exception) {
                $this->flash->addMessage('error', $this->translator->trans($exception->getMessage()));
                $statusCode = 400;
            }
        }

        return $this->respondWithJson($response, [
            'route' => 'admin.roles.index',
            'user' => $user,
            'roles' => array_values($roleDtoMap),
            'selectedRole' => $selectedRole,
            'selectedId' => $selectedId,
            'permissionGroups' => array_map(static fn($group) => $group->toArray(), $permissionMatrix->getGroups()),
            'selectedPermissions' => $selectedRole['permissionKeys'] ?? [],
            'messages' => $this->flash->getMessages(),
        ], $statusCode);
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

}
