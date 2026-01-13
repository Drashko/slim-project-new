<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Digiboard;

use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DigiboardPageController
{
    private const DEFAULT_PAGE = 'crm-dashboard';

    public function __construct(
        private TemplateRenderer $templates,
        private AdminAuthenticator $authenticator,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = $this->authenticator->authenticate($request);

        $page = (string)($args['page'] ?? self::DEFAULT_PAGE);
        $page = trim($page);
        $page = str_replace(['\\', '..'], ['', ''], $page);
        $page = ltrim($page, '/');
        $page = preg_replace('/\.(html|php)$/i', '', $page) ?? $page;

        // Friendly aliases
        if ($page === '' || $page === 'dashboard') {
            $page = self::DEFAULT_PAGE;
        }

        // Only allow existing templates
        $template = 'admin::digiboard/' . $page;
        $templatePath = __DIR__ . '/../../../../../templates/admin/digiboard/' . $page . '.php';
        if (!is_file($templatePath)) {
            // 404
            $response = $response->withStatus(404);
            return $this->templates->render($response, 'admin::digiboard/error-404', [
                'user' => $user,
                'requested_page' => $page,
            ]);
        }

        return $this->templates->render($response, $template, [
            'user' => $user,
        ]);
    }
}
