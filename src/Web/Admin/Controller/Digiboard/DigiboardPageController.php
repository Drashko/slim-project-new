<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Digiboard;

use App\Integration\Auth\AdminAuthenticator;
use App\Web\Shared\LocalizedRouteTrait;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DigiboardPageController
{
    use LocalizedRouteTrait;

    private const DEFAULT_PAGE = 'crm-dashboard';

    public function __construct(
        private Engine $engine,
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
        $baseHref = $this->localizedPath($request, 'admin') . '/';

        if (!is_file($templatePath)) {
            $output = $this->engine->render('admin::digiboard/error-404', [
                'user' => $user,
                'requested_page' => $page,
            ]);

            return $this->writeResponse($response->withStatus(404), $this->injectBaseHref($output, $baseHref));
        }

        $output = $this->engine->render($template, [
            'user' => $user,
        ]);

        return $this->writeResponse($response, $this->injectBaseHref($output, $baseHref));
    }

    private function writeResponse(ResponseInterface $response, string $output): ResponseInterface
    {
        $response->getBody()->write($output);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    private function injectBaseHref(string $html, string $baseHref): string
    {
        if (stripos($html, '<base') !== false) {
            return $html;
        }

        $baseTag = '<base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '">';
        $updated = preg_replace('/<head(\s[^>]*)?>/i', '$0' . "\n    " . $baseTag, $html, 1);

        return $updated ?? $html;
    }
}
