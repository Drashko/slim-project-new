<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\User;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Role\Command\ListRolesCommand;
use App\Feature\Admin\Role\Handler\ListRolesHandler;
use App\Feature\Admin\User\Command\CreateUserCommand;
use App\Feature\Admin\User\Handler\CreateUserHandler;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Web\Admin\Service\UserService;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

final readonly class UserCreateController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer   $templates,
        private AdminAuthenticator $authenticator,
        private CreateUserHandler  $createUser,
        private Messages           $flash,
        private UserService        $userDirectory,
        private ListRolesHandler   $listRoles,
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

        if (strtoupper($request->getMethod()) === 'POST') {
            $response = $this->handleCreate($request, $response);

            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/users'))
                ->withStatus($response->getStatusCode());
        }

        $directory = $this->userDirectory->all();
        $availableRoles = $this->availableRoles();

        return $this->templates->render($response, 'admin::users/create', [
            'user' => $user,
            'flash' => $this->flash,
            'roles' => $availableRoles,
            'statuses' => $this->userDirectory->statuses($directory),
        ]);
    }

    private function handleCreate(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
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

            return $response->withStatus(302);
        } catch (\Throwable $exception) {
            $this->flash->addMessage('admin_error', $exception->getMessage());

            return $response->withStatus(400);
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
