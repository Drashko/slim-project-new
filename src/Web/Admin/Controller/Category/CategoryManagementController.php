<?php

declare(strict_types=1);

namespace App\Web\Admin\Controller\Category;

use App\Domain\Category\Category;
use App\Domain\Category\CategoryInterface;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Integration\Auth\AdminAuthenticator;
use App\Integration\View\TemplateRenderer;
use App\Web\Shared\LocalizedRouteTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Flash\Messages;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CategoryManagementController
{
    use LocalizedRouteTrait;

    public function __construct(
        private TemplateRenderer $templates,
        private AdminAuthenticator $authenticator,
        private CategoryRepositoryInterface $categories,
        private Messages $flash,
        private TranslatorInterface $translator,
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

        if ($request->getMethod() === 'POST') {
            $payload = (array) ($request->getParsedBody() ?? []);
            $action = (string) ($payload['action'] ?? '');

            try {
                if ($action === 'save_category') {
                    $this->handleSave($payload);
                    $messageKey = empty($payload['category_id'])
                        ? 'admin.categories.flash.created'
                        : 'admin.categories.flash.updated';

                    $this->flash->addMessage('success', $this->translator->trans($messageKey));

                    return $response
                        ->withHeader('Location', $this->localizedPath($request, 'admin/categories'))
                        ->withStatus(302);
                }

                if ($action === 'delete_category') {
                    $this->handleDelete($payload);
                    $this->flash->addMessage('success', $this->translator->trans('admin.categories.flash.deleted'));

                    return $response
                        ->withHeader('Location', $this->localizedPath($request, 'admin/categories'))
                        ->withStatus(302);
                }
            } catch (\InvalidArgumentException $exception) {
                $this->flash->addMessage('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->flash->addMessage('error', $this->translator->trans('admin.categories.flash.error'));
            }
        }

        $query = $request->getQueryParams();
        $selectedId = is_string($query['category'] ?? null) ? (string) $query['category'] : null;
        $selectedCategory = $selectedId ? $this->categories->findById($selectedId) : null;
        $allCategories = $this->categories->all();

        return $this->templates->render($response, 'admin::categories/index', [
            'user' => $user,
            'flash' => $this->flash,
            'categories' => array_map([$this, 'normalizeCategory'], $allCategories),
            'parentOptions' => $this->buildParentOptions($allCategories, $selectedCategory),
            'selected' => $selectedCategory ? $this->normalizeCategory($selectedCategory) : null,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleSave(array $payload): void
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $parentId = trim((string) ($payload['parent_id'] ?? ''));
        $categoryId = trim((string) ($payload['category_id'] ?? ''));
        $parent = $parentId !== '' ? $this->categories->findById($parentId) : null;

        if ($categoryId !== '') {
            $existing = $this->categories->findById($categoryId);
            if (!$existing instanceof CategoryInterface) {
                throw new \InvalidArgumentException('Category not found.');
            }

            if ($parent instanceof CategoryInterface && $parent->getId() === $existing->getId()) {
                throw new \InvalidArgumentException('Category cannot be its own parent.');
            }

            $existing->setName($name);
            $existing->setParent($parent);
            $this->categories->add($existing);
        } else {
            $category = new Category($name, $parent);
            $this->categories->add($category);
        }

        $this->categories->flush();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleDelete(array $payload): void
    {
        $categoryId = trim((string) ($payload['category_id'] ?? ''));
        if ($categoryId === '') {
            throw new \InvalidArgumentException('Category not found.');
        }

        $category = $this->categories->findById($categoryId);
        if (!$category instanceof CategoryInterface) {
            throw new \InvalidArgumentException('Category not found.');
        }

        $this->categories->remove($category);
        $this->categories->flush();
    }

    private function normalizeCategory(CategoryInterface $category): array
    {
        $parent = $category->getParent();

        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'parent_id' => $parent?->getId(),
            'parent_name' => $parent?->getName(),
        ];
    }

    /**
     * @param CategoryInterface[] $categories
     *
     * @return array<int, array{id: string, label: string}>
     */
    private function buildParentOptions(array $categories, ?CategoryInterface $selected): array
    {
        $labels = $this->buildCategoryLabels($categories);
        $options = [];
        foreach ($categories as $category) {
            if ($selected instanceof CategoryInterface && $category->getId() === $selected->getId()) {
                continue;
            }

            $options[] = [
                'id' => $category->getId(),
                'label' => $labels[$category->getId()] ?? $category->getName(),
            ];
        }

        usort(
            $options,
            static fn(array $left, array $right): int => strcmp($left['label'], $right['label'])
        );

        return $options;
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
