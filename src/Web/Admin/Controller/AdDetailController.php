<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller;

use App\Domain\Category\CategoryInterface;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Ad\Command\UpdateAdCommand;
use App\Feature\Ad\Handler\GetAdHandler;
use App\Feature\Ad\Handler\UpdateAdHandler;
use App\Feature\Ad\Query\GetAdQuery;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\Flash\FlashMessages;
use App\Integration\Helper\ImageStorage;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

final readonly class AdDetailController
{
    use LocalizedRouteTrait;

    /**
     * @var string[]
     */
    private array $statuses;

    public function __construct(
        private TemplateRenderer $templates,
        private AdminAuthenticator $authenticator,
        private GetAdHandler $getAdHandler,
        private UpdateAdHandler $updateAdHandler,
        private CategoryRepositoryInterface $categories,
        private ImageStorage $imageStorage,
        private FlashMessages $flash,
    ) {
        $this->statuses = ['Pending', 'Published', 'Archived'];
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $user = $this->authenticator->authenticate($request);
        } catch (DomainException) {
            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/login'))
                ->withStatus(302);
        }

        $adId = (string) ($args['id'] ?? '');

        try {
            $ad = $this->getAdHandler->handle(new GetAdQuery($adId));
        } catch (DomainException $exception) {
            $this->flash->addMessage('admin_error', $exception->getMessage());

            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/ads'))
                ->withStatus(302);
        }

        if (strtoupper($request->getMethod()) === 'POST') {
            $response = $this->handleUpdate($request, $response, $adId);

            return $response
                ->withHeader('Location', $this->localizedPath($request, 'admin/ads/' . rawurlencode($adId)))
                ->withStatus($response->getStatusCode());
        }

        return $this->templates->render($response, 'admin::ads/detail', [
            'user' => $user,
            'ad' => [
                'id' => $ad->getId(),
                'title' => $ad->getTitle(),
                'description' => $ad->getDescription(),
                'category' => $ad->getCategory(),
                'status' => $ad->getStatus(),
                'images' => $ad->getImages(),
                'created_at' => $ad->getCreatedAt()->format('Y-m-d H:i'),
                'updated_at' => $ad->getUpdatedAt()->format('Y-m-d H:i'),
            ],
            'statuses' => $this->statuses,
            'categories' => $this->getCategories(),
            'flash' => $this->flash,
        ]);
    }

    private function handleUpdate(ServerRequestInterface $request, ResponseInterface $response, string $adId): ResponseInterface
    {
        $payload = (array) ($request->getParsedBody() ?? []);
        $uploadedFiles = $request->getUploadedFiles();
        $imageFiles = $uploadedFiles['images'] ?? null;
        $shouldReplaceImages = $this->hasValidUpload($imageFiles);
        $images = $shouldReplaceImages ? $this->imageStorage->store($imageFiles) : null;

        try {
            $this->updateAdHandler->handle(new UpdateAdCommand(
                $adId,
                (string) ($payload['title'] ?? ''),
                (string) ($payload['description'] ?? ''),
                (string) ($payload['category'] ?? ''),
                (string) ($payload['status'] ?? 'Pending'),
                $images
            ));

            $this->flash->addMessage('admin_success', 'Advertisement updated successfully.');

            return $response->withStatus(302);
        } catch (DomainException|\Throwable $exception) {
            $this->flash->addMessage('admin_error', $exception->getMessage());

            return $response->withStatus(400);
        }
    }

    private function hasValidUpload(mixed $files): bool
    {
        if ($files instanceof UploadedFileInterface) {
            return $files->getError() === \UPLOAD_ERR_OK;
        }

        if (!is_array($files)) {
            return false;
        }

        foreach ($files as $file) {
            if ($file instanceof UploadedFileInterface && $file->getError() === \UPLOAD_ERR_OK) {
                return true;
            }
        }

        return false;
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
