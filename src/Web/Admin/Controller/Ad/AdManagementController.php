<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Ad;

use App\Domain\Ad\AdInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Ad\Handler\ListAdsHandler;
use App\Feature\Ad\Query\ListAdsQuery;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;

final readonly class AdManagementController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer $templates,
        private AdminAuthenticator $authenticator,
        private ListAdsHandler $listAdsHandler,
        private Messages $flash,
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

        $queryParams = $request->getQueryParams();
        $category = $this->normalizeString($queryParams['category'] ?? null);
        $status = $this->normalizeStatus($queryParams['status'] ?? null);
        $userFilter = $this->normalizeString($queryParams['user'] ?? null);
        $fromDate = $this->normalizeDate($queryParams['from_date'] ?? null, false);
        $toDate = $this->normalizeDate($queryParams['to_date'] ?? null, true);

        $ads = $this->listAdsHandler->handle(new ListAdsQuery(
            null,
            $category,
            $status,
            $userFilter,
            $fromDate,
            $toDate
        ));

        return $this->templates->render($response, 'admin::ads/index', [
            'user' => $user,
            'ads' => array_map($this->normalizeAd(), $ads),
            'flash' => $this->flash,
            'filters' => [
                'category' => $category,
                'status' => $status,
                'user' => $userFilter,
                'from_date' => $fromDate?->format('Y-m-d'),
                'to_date' => $toDate?->format('Y-m-d'),
            ],
            'statuses' => ['Pending', 'Published', 'Archived'],
        ]);
    }

    private function normalizeAd(): callable
    {
        return static function (AdInterface $ad): array {
            return [
                'id' => $ad->getId(),
                'title' => $ad->getTitle(),
                'category' => $ad->getCategory(),
                'status' => $ad->getStatus(),
                'created_at' => $ad->getCreatedAt()->format('Y-m-d H:i'),
                'updated_at' => $ad->getUpdatedAt()->format('Y-m-d H:i'),
            ];
        };
    }

    private function normalizeString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeStatus(mixed $value): ?string
    {
        $normalized = $this->normalizeString($value);
        if ($normalized === null) {
            return null;
        }

        $normalized = ucfirst(strtolower($normalized));
        $allowed = ['Pending', 'Published', 'Archived'];

        return in_array($normalized, $allowed, true) ? $normalized : null;
    }

    private function normalizeDate(mixed $value, bool $endOfDay): ?\DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $normalized);
        if (!$date instanceof \DateTimeImmutable) {
            return null;
        }

        return $endOfDay
            ? $date->setTime(23, 59, 59)
            : $date->setTime(0, 0, 0);
    }
}
