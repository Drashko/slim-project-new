<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Role\Command\ListRolesCommand;
use App\Feature\Admin\Role\Handler\ListRolesHandler;
use App\Feature\Admin\User\Command\CreateUserCommand;
use App\Feature\Admin\User\Handler\CreateUserHandler;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Web\Admin\Service\UserService;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserCreateController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private AdminAuthenticator $authenticator,
        private CreateUserHandler  $createUser,
        private FlashMessages      $flash,
        private UserService        $userDirectory,
        private ListRolesHandler   $listRoles,
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

        if (strtoupper($request->getMethod()) === 'POST') {
            $result = $this->handleCreate($request);
            $status = $result['success'] ? 201 : 400;

            return $this->respondWithJson($response, [
                'status' => $result['success'] ? 'created' : 'error',
                'redirect' => $this->localizedPath($request, 'admin/users'),
                'messages' => $this->flash->getMessages(),
                'error' => $result['error'] ?? null,
            ], $status);
        }

        $directory = $this->userDirectory->all();
        $availableRoles = $this->availableRoles();

        return $this->respondWithJson($response, [
            'route' => 'admin.users.create',
            'user' => $user,
            'roles' => $availableRoles,
            'statuses' => $this->userDirectory->statuses($directory),
            'messages' => $this->flash->getMessages(),
        ]);
    }

    /**
     * @return array{success: bool, error?: string}
     */
    private function handleCreate(ServerRequestInterface $request): array
    {
        $payload = (array) ($request->getParsedBody() ?? []);

        try {
            $this->createUser->handle(new CreateUserCommand(
                (string) ($payload['email'] ?? ''),
                (string) ($payload['password'] ?? ''),
                $this->extractRoles($payload['roles'] ?? []),
                (string) ($payload['status'] ?? 'Active')
            ));

            $this->flash->addMessage('admin_success', 'User created successfully.');

            return ['success' => true];
        } catch (\Throwable $exception) {
            $this->flash->addMessage('admin_error', $exception->getMessage());

            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param mixed $roles
     * @return string[]
     */
    private function extractRoles(mixed $roles): array
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        if (!is_array($roles)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn(mixed $value): string => strtoupper(trim((string) $value)),
            $roles
        ), static fn(string $role): bool => $role !== ''));
    }

    /**
     * @return array<int, array{key: string, name: string, description: string, critical: bool}>
     */
    private function availableRoles(): array
    {
        $roles = [];

        foreach ($this->listRoles->handle(new ListRolesCommand()) as $role) {
            $roles[] = [
                'key' => strtoupper($role->getId()),
                'name' => $role->getName(),
                'description' => $role->getDescription(),
                'critical' => $role->isCritical(),
            ];
        }

        return $roles;
    }
}
