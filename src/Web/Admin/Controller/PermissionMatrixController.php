<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\CreatePermissionCommand;
use App\Feature\Admin\Permission\Command\DeletePermissionCommand;
use App\Feature\Admin\Permission\Command\ListPermissionsCommand;
use App\Feature\Admin\Permission\DtoPermissionRequest;
use App\Feature\Admin\Permission\Handler\CreatePermissionHandler;
use App\Feature\Admin\Permission\Handler\DeletePermissionHandler;
use App\Feature\Admin\Permission\Handler\ListPermissionsHandler;
use App\Feature\Admin\Permission\ValidatePermissionRequest;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PermissionMatrixController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private AdminAuthenticator $authenticator,
        private ListPermissionsHandler $permissions,
        private ValidatePermissionRequest $validator,
        private CreatePermissionHandler $createPermission,
        private DeletePermissionHandler $deletePermission,
        private FlashMessages $flash,
        private TranslatorInterface $translator,
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

        $statusCode = 200;

        if ($request->getMethod() === 'POST') {
            $body = (array) ($request->getParsedBody() ?? []);
            $action = (string) ($body['action'] ?? '');

            try {
                if ($action === 'create_permission') {
                    $this->createPermission->handle(new CreatePermissionCommand(
                        (string) ($body['permission_key'] ?? ''),
                        (string) ($body['permission_label'] ?? ''),
                    ));

                    $this->flash->addMessage('success', $this->translator->trans('admin.permissions.flash.created'));

                    return $this->respondWithJson($response, [
                        'status' => 'created',
                        'redirect' => $this->localizedPath($request, 'admin/permissions'),
                        'messages' => $this->flash->getMessages(),
                    ]);
                }

                if ($action === 'delete_permission') {
                    $this->deletePermission->handle(new DeletePermissionCommand((string) ($body['permission_key'] ?? '')));

                    $this->flash->addMessage('success', $this->translator->trans('admin.permissions.flash.deleted'));

                    return $this->respondWithJson($response, [
                        'status' => 'deleted',
                        'redirect' => $this->localizedPath($request, 'admin/permissions'),
                        'messages' => $this->flash->getMessages(),
                    ]);
                }
            } catch (DomainException $exception) {
                $this->flash->addMessage('error', $this->translator->trans($exception->getMessage()));
                $statusCode = 400;
            }
        }

        $input = new DtoPermissionRequest($request->getQueryParams());
        $errors = $this->validator->validate($input);

        if ($errors !== []) {
            $sanitized = [
                'q' => mb_substr($input->getSearch(), 0, 255),
                'granted' => $input->getGranted(),
            ];

            $input = new DtoPermissionRequest($sanitized);
        }

        $result = $this->permissions->handle(new ListPermissionsCommand($input));

        return $this->respondWithJson($response, [
            'route' => 'admin.permissions.index',
            'user' => $user,
            'groups' => array_map(static fn($group) => $group->toArray(), $result->getGroups()),
            'search' => $input->getSearch(),
            'granted' => $result->getGranted(),
            'totalPermissions' => $result->getTotalPermissions(),
            'messages' => $this->flash->getMessages(),
        ], $statusCode);
    }
}
