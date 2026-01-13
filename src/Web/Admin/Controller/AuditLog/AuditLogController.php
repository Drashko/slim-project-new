<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\AuditLog;

use App\Domain\Shared\DomainException;
use App\Feature\Admin\Audit\Handler\ListDomainEventLogsHandler;
use App\Feature\Admin\Audit\Command\ListDomainEventLogsCommand;
use App\Feature\Admin\Audit\ListDomainEventLogsResult;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuditLogController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer $templates,
        private AdminAuthenticator $authenticator,
        private ListDomainEventLogsHandler $listDomainEventLogs,
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

        $params = $request->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $aggregateId = $this->extractString($params, 'aggregate_id');
        $aggregateType = $this->extractString($params, 'aggregate_type');
        $eventType = $this->extractString($params, 'event_type');
        $processedParam = $this->extractString($params, 'processed');
        $processed = null;

        if ($processedParam === 'processed') {
            $processed = true;
        } elseif ($processedParam === 'pending') {
            $processed = false;
        }

        $result = $this->listDomainEventLogs->handle(
            new ListDomainEventLogsCommand(
                $page,
                50,
                $aggregateId,
                $aggregateType,
                $eventType,
                $processed,
            ),
        );

        return $this->templates->render($response, 'admin::audit/index', [
            'user' => $user,
            'logs' => $this->mapLogs($result),
            'total' => $result->getTotal(),
            'page' => $result->getPage(),
            'pageSize' => $result->getPageSize(),
            'filters' => [
                'aggregateId' => $aggregateId,
                'aggregateType' => $aggregateType,
                'eventType' => $eventType,
                'processed' => $processedParam,
            ],
        ]);
    }

    private function extractString(array $params, string $key): ?string
    {
        $value = isset($params[$key]) ? (string) $params[$key] : null;
        $value = $value !== null ? trim($value) : null;

        return $value !== '' ? $value : null;
    }

    /**
     * @throws \JsonException
     */
    private function mapLogs(ListDomainEventLogsResult $result): array
    {
        $formatted = [];

        foreach ($result->getLogs() as $log) {
            $payload = json_encode($log->getPayload(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $formatted[] = [
                'aggregateId' => $log->getAggregateId(),
                'aggregateType' => $log->getAggregateType(),
                'eventType' => $log->getEventType(),
                'payload' => $payload ?: '{}',
                'occurredAt' => $log->getOccurredAt()->format('Y-m-d H:i:s'),
                'processed' => $log->isProcessed(),
                'processedAt' => $log->getProcessedAt()?->format('Y-m-d H:i:s'),
            ];
        }

        return $formatted;
    }
}
