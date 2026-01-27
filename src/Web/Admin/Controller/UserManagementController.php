<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\View\TemplateRenderer;
use App\Web\Admin\Service\UserService;
use App\Web\Shared\LocalizedRouteTrait;
use App\Web\Shared\Paginator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserManagementController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer   $templates,
        private AdminAuthenticator $authenticator,
        private UserService        $userDirectory,
        private Paginator          $paginator,
        private FlashMessages      $flash,
        private array              $settings
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

        $directory = $this->userDirectory->all();
        $filters = $this->resolveFilters($request);
        $filteredDirectory = $this->applyFilters($directory, $filters);
        $pagination = $this->paginator->paginate(
            $filteredDirectory,
            $this->resolvePage($request),
            $this->resolvePerPage()
        );

        $roles = $this->userDirectory->roles($directory);
        $statuses = $this->userDirectory->statuses($directory);
        $queryParams = $request->getQueryParams();

        return $this->templates->render($response, 'admin::users/index', [
            'user' => $user,
            'directory' => $pagination['items'],
            'filters' => $filters,
            'roles' => $roles,
            'statuses' => $statuses,
            'totalUsers' => count($directory),
            'pagination' => $pagination,
            'queryParams' => $queryParams,
            'flash' => $this->flash,
        ]);
    }

    /**
     * @return array{query: string, role: string, status: string}
     */
    private function resolveFilters(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();

        return [
            'query' => trim((string) ($params['query'] ?? '')),
            'role' => (string) ($params['role'] ?? 'all'),
            'status' => (string) ($params['status'] ?? 'all'),
        ];
    }

    private function resolvePage(ServerRequestInterface $request): int
    {
        $params = $request->getQueryParams();

        return max(1, (int) ($params['page'] ?? 1));
    }

    private function resolvePerPage(): int
    {
        $paginationSettings = (array) ($this->settings['pagination'] ?? []);
        $defaultPerPage = (int) ($paginationSettings['default_per_page'] ?? 10);
        $adminUsersPerPage = (int) ($paginationSettings['admin_users_per_page'] ?? 0);

        $perPage = $adminUsersPerPage > 0 ? $adminUsersPerPage : $defaultPerPage;

        return max(1, $perPage);
    }

    /**
     * @param array<int, array<string, mixed>> $directory
     * @return array<int, array<string, mixed>>
     */
    private function applyFilters(array $directory, array $filters): array
    {
        return array_values(array_filter(
            $directory,
            static function (array $member) use ($filters): bool {
                $query = strtolower($filters['query']);
                $matchesQuery = $query === ''
                    || str_contains(strtolower((string) ($member['name'] ?? '')), $query)
                    || str_contains(strtolower((string) ($member['email'] ?? '')), $query);

                $matchesRole = $filters['role'] === 'all'
                    || strcasecmp((string) ($member['role'] ?? ''), $filters['role']) === 0;

                $matchesStatus = $filters['status'] === 'all'
                    || strcasecmp((string) ($member['status'] ?? ''), $filters['status']) === 0;

                return $matchesQuery && $matchesRole && $matchesStatus;
            }
        ));
    }

}
