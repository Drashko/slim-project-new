<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Role\Command\ListRolesCommand;
use App\Feature\Admin\Role\Handler\ListRolesHandler;
use App\Feature\Admin\User\Command\DeleteUserCommand;
use App\Feature\Admin\User\Command\UpdateUserCommand;
use App\Feature\Admin\User\Handler\DeleteUserHandler;
use App\Feature\Admin\User\Handler\UpdateUserHandler;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\JsonResponseTrait;
use App\Web\Admin\Service\UserService;
use App\Web\Shared\LocalizedRouteTrait;
use DateInterval;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserDetailController
{
    use LocalizedRouteTrait;
    use JsonResponseTrait;

    public function __construct(
        private AdminAuthenticator $authenticator,
        private UserService        $directory,
        private UpdateUserHandler  $updateUser,
        private DeleteUserHandler  $deleteUser,
        private FlashMessages      $flash,
        private ListRolesHandler   $listRoles,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $this->respondWithJson($response, [
                'error' => 'Unauthorized',
                'redirect' => $this->localizedPath($request, 'admin/login'),
            ], 401);
        }

        $memberId = (string) ($args['id'] ?? '');
        $member = $memberId !== '' ? $this->directory->find($memberId) : null;

        if ($member !== null && strtoupper($request->getMethod()) === 'POST') {
            $result = $this->handleAction($request, $memberId);
            $status = $result['success'] ? 200 : 400;

            return $this->respondWithJson($response, [
                'status' => $result['success'] ? 'updated' : 'error',
                'redirect' => $this->localizedPath($request, 'admin/users/' . $memberId),
                'messages' => $this->flash->getMessages(),
                'error' => $result['error'] ?? null,
            ], $status);
        }

        $payload = [
            'route' => 'admin.users.detail',
            'user' => $user,
            'member' => $member,
            'roles' => $this->availableRoles(),
            'contact' => $member !== null ? $this->buildContact($member) : [],
            'timeline' => $member !== null ? $this->buildTimeline($member) : [],
            'activity' => $member !== null ? $this->buildActivity($member) : [],
            'messages' => $this->flash->getMessages(),
        ];

        if ($member === null) {
            return $this->respondWithJson($response, $payload, 404);
        }

        return $this->respondWithJson($response, $payload);
    }

    /**
     * @return array{success: bool, error?: string}
     */
    private function handleAction(ServerRequestInterface $request, string $userId): array
    {
        $payload = (array) ($request->getParsedBody() ?? []);
        $action = strtoupper((string) ($payload['_action'] ?? ''));

        try {
            if ($action === 'DELETE') {
                $this->deleteUser->handle(new DeleteUserCommand($userId));
                $this->flash->addMessage('admin_success', 'User deleted successfully.');

                return ['success' => true];
            }

            $this->updateUser->handle(new UpdateUserCommand(
                $userId,
                array_key_exists('email', $payload) ? (string) $payload['email'] : null,
                array_key_exists('password', $payload) ? (string) $payload['password'] : null,
                array_key_exists('roles', $payload) ? $this->extractRoles($payload['roles']) : null,
                array_key_exists('status', $payload) ? (string) $payload['status'] : null,
            ));

            $this->flash->addMessage('admin_success', 'User updated successfully.');

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

    /**
     * @return array<string, string>
     */
    private function buildContact(array $member): array
    {
        return [
            'phone' => '',
            'location' => (string) ($member['role'] ?? ''),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildTimeline(array $member): array
    {
        $createdAt = $this->parseDate($member['created_at'] ?? null);
        $updatedAt = $this->parseDate($member['updated_at'] ?? null);

        if ($createdAt === null && $updatedAt === null) {
            return [];
        }

        return array_values(array_filter([
            $createdAt !== null ? ['key' => 'invited', 'date' => $createdAt->format('Y-m-d')] : null,
            $createdAt !== null
                ? ['key' => 'joined', 'date' => $createdAt->add(new DateInterval('P7D'))->format('Y-m-d')]
                : null,
            $updatedAt !== null ? ['key' => 'reviewed', 'date' => $updatedAt->format('Y-m-d')] : null,
        ]));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildActivity(array $member): array
    {
        $name = (string) ($member['name'] ?? 'User');
        $role = (string) ($member['role'] ?? 'Team');
        $status = strtolower((string) ($member['status'] ?? 'active'));
        $createdAt = $this->parseDate($member['created_at'] ?? null);
        $updatedAt = $this->parseDate($member['updated_at'] ?? null) ?? new DateTimeImmutable('now');

        return array_values(array_filter([
            $createdAt !== null ? [
                'title' => 'Permissions updated',
                'timestamp' => $createdAt->format('Y-m-d H:i'),
                'detail' => sprintf('%s joined with %s permissions.', $name, $role),
            ] : null,
            [
                'title' => 'Status review',
                'timestamp' => $updatedAt->sub(new DateInterval('P3D'))->format('Y-m-d H:i'),
                'detail' => sprintf('Account confirmed as %s.', $status),
            ],
            [
                'title' => 'Login event',
                'timestamp' => $updatedAt->format('Y-m-d H:i'),
                'detail' => 'Session created from admin workspace.',
            ],
        ]));
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
