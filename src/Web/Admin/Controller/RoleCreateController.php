<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\ListPermissionsCommand;
use App\Feature\Admin\Permission\DtoPermissionRequest;
use App\Feature\Admin\Permission\Handler\ListPermissionsHandler;
use App\Feature\Admin\Role\Command\CreateRoleCommand;
use App\Feature\Admin\Role\Handler\CreateRoleHandler;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RoleCreateController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private AdminAuthenticator $authenticator,
        private ListPermissionsHandler $listPermissions,
        private CreateRoleHandler $createRole,
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

        $permissionMatrix = $this->listPermissions->handle(
            new ListPermissionsCommand(new DtoPermissionRequest([]))
        );

        $statusCode = 200;

        if ($request->getMethod() === 'POST') {
            $body = (array) ($request->getParsedBody() ?? []);

            try {
                $created = $this->createRole->handle(new CreateRoleCommand(
                    (string) ($body['role_key'] ?? ''),
                    (string) ($body['name'] ?? ''),
                    (string) ($body['description'] ?? ''),
                    $this->extractPermissions($body),
                    isset($body['critical'])
                ));

                $this->flash->addMessage('success', $this->translator->trans('admin.roles.flash.created'));

                return $this->respondWithJson($response, [
                    'status' => 'created',
                    'redirect' => $this->localizedPath($request, 'admin/roles') . '?role=' . urlencode($created->getKey()),
                    'messages' => $this->flash->getMessages(),
                ], 201);
            } catch (DomainException $exception) {
                $this->flash->addMessage('error', $this->translator->trans($exception->getMessage()));
                $statusCode = 400;
            }
        }

        return $this->respondWithJson($response, [
            'route' => 'admin.roles.new',
            'user' => $user,
            'allPermissions' => $this->flattenPermissions($permissionMatrix->getGroups()),
            'messages' => $this->flash->getMessages(),
        ], $statusCode);
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
