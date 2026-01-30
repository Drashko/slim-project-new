<?php

declare(strict_types=1);

namespace App\Web\Public\Controller;

use App\Domain\Ad\AdInterface;
use App\Domain\Category\CategoryInterface;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Ad\Command\CreateAdCommand;
use App\Feature\Ad\Handler\CreateAdHandler;
use App\Feature\Ad\Handler\ListAdsHandler;
use App\Feature\Ad\Query\ListAdsQuery;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\ImageStorage;
use App\Integration\Session\PublicSessionInterface;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use App\Web\Shared\PublicUserResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AdsController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer $templates,
        private PublicSessionInterface $session,
        private CreateAdHandler $createAdHandler,
        private ListAdsHandler $listAdsHandler,
        private CategoryRepositoryInterface $categories,
        private ImageStorage $imageStorage,
        private FlashMessages $flash,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $normalizedUser = PublicUserResolver::resolve($this->session->get('user'));

        if ($normalizedUser === null || !isset($normalizedUser['id'])) {
            return $response
                ->withHeader('Location', $this->localizedPath($request, 'auth/login'))
                ->withStatus(302);
        }

        $statusCode = 200;

        if (strtoupper($request->getMethod()) === 'POST') {
            $created = $this->handleCreate($request, $normalizedUser['id']);
            if ($created) {
                return $response
                    ->withHeader('Location', $this->localizedPath($request, 'profile/ads'))
                    ->withStatus(302);
            }

            $statusCode = 400;
        }

        $ads = $this->listAdsHandler->handle(new ListAdsQuery($normalizedUser['id']));

        $rendered = $this->templates->render($response, 'profile::ads', [
            'user' => $normalizedUser,
            'flash' => $this->flash,
            'categories' => $this->getCategories(),
            'ads' => array_map($this->normalizeAd(), $ads),
        ]);

        return $rendered->withStatus($statusCode);
    }

    private function handleCreate(ServerRequestInterface $request, string $userId): bool
    {
        $payload = (array) ($request->getParsedBody() ?? []);
        $uploadedFiles = $request->getUploadedFiles();
        $imageFiles = $uploadedFiles['images'] ?? [];
        $images = $this->imageStorage->store($imageFiles);

        try {
            $this->createAdHandler->handle(new CreateAdCommand(
                $userId,
                (string) ($payload['title'] ?? ''),
                (string) ($payload['description'] ?? ''),
                (string) ($payload['category'] ?? ''),
                $images,
                'Pending'
            ));

            $this->flash->addMessage('success', 'Ad submitted successfully and is pending review.');

            return true;
        } catch (DomainException|\Throwable $exception) {
            $this->flash->addMessage('error', $exception->getMessage());

            return false;
        }
    }

    private function normalizeAd(): callable
    {
        return static function (AdInterface $ad): array {
            return [
                'id' => $ad->getId(),
                'title' => $ad->getTitle(),
                'description' => $ad->getDescription(),
                'category' => $ad->getCategory(),
                'status' => $ad->getStatus(),
                'images' => $ad->getImages(),
                'created_at' => $ad->getCreatedAt()->format('Y-m-d H:i'),
            ];
        };
    }

    /**
     * @return string[]
     */
    private function getCategories(): array
    {
        $categories = $this->categories->all();
        if ($categories === []) {
            return [];
        }

        $labels = $this->buildCategoryLabels($categories);
        asort($labels);

        return array_values($labels);
    }

    /**
     * @param CategoryInterface[] $categories
     *
     * @return array<string, string>
     */
    private function buildCategoryLabels(array $categories): array
    {
        $labels = [];
        foreach ($categories as $category) {
            $labels[$category->getId()] = $this->resolveCategoryLabel($category);
        }

        return $labels;
    }

    private function resolveCategoryLabel(CategoryInterface $category): string
    {
        $parts = [$category->getName()];
        $parent = $category->getParent();

        while ($parent instanceof CategoryInterface) {
            $parts[] = $parent->getName();
            $parent = $parent->getParent();
        }

        return implode(' / ', array_reverse($parts));
    }
}
