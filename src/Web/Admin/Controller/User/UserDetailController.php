<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\User;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Feature\Admin\User\Command\DeleteUserCommand;
use App\Feature\Admin\User\Command\UpdateUserCommand;
use App\Feature\Admin\User\Handler\DeleteUserHandler;
use App\Feature\Admin\User\Handler\UpdateUserHandler;
use App\Web\Admin\Service\UserService;
use App\Web\Shared\LocalizedRouteTrait;
use DateInterval;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

final readonly class UserDetailController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer   $templates,
        private AdminAuthenticator $authenticator,
        private UserService        $directory,
        private UpdateUserHandler  $updateUser,
        private DeleteUserHandler  $deleteUser,
        private Messages           $flash,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/login'))
                ->withStatus(302);
        }

        $memberId = (string) ($args['id'] ?? '');
        $member = $memberId !== '' ? $this->directory->find($memberId) : null;

        if ($member !== null && strtoupper($request->getMethod()) === 'POST') {
            $response = $this->handleAction($request, $response, $memberId);

            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/users/' . $memberId))
                ->withStatus($response->getStatusCode());
        }

        $payload = [
            'user' => $user,
            'member' => $member,
            'contact' => $member !== null ? $this->buildContact($member) : [],
            'timeline' => $member !== null ? $this->buildTimeline($member) : [],
            'activity' => $member !== null ? $this->buildActivity($member) : [],
        ];

        if ($member === null) {
            return $this->templates->render($response->withStatus(404), 'admin::users/detail', $payload);
        }

        return $this->templates->render($response, 'admin::users/detail', $payload);
    }

    private function handleAction(ServerRequestInterface $request, ResponseInterface $response, string $userId): ResponseInterface
    {
        $payload = (array) ($request->getParsedBody() ?? []);
        $action = strtoupper((string) ($payload['_action'] ?? ''));

        try {
            if ($action === 'DELETE') {
                $this->deleteUser->handle(new DeleteUserCommand($userId));
                $this->flash->addMessage('admin_success', 'User deleted successfully.');

                return $response
                    ->withHeader('Location', $this->localizedPath($request, 'admin/users'))
                    ->withStatus(302);
            }

            $this->updateUser->handle(new UpdateUserCommand(
                $userId,
                array_key_exists('email', $payload) ? (string) $payload['email'] : null,
                array_key_exists('password', $payload) ? (string) $payload['password'] : null,
                array_key_exists('roles', $payload) ? $this->extractRoles($payload['roles']) : null,
                array_key_exists('status', $payload) ? (string) $payload['status'] : null,
            ));

            $this->flash->addMessage('admin_success', 'User updated successfully.');

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
