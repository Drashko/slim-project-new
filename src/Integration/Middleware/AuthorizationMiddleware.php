<?php

declare(strict_types=1);

namespace App\Integration\Middleware;

use App\Domain\Auth\Identity;
use App\Integration\Rbac\Policy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final  class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @param string[] $requiredAbilities
     */
    public function __construct(
        private array                             $requiredAbilities,
        private readonly Policy                   $policy,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly string $forbiddenRoute = '/forbidden'
    ) {
        $this->requiredAbilities = $this->normalizeAbilities($requiredAbilities);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Identity|null $identity */
        $identity = $request->getAttribute(Identity::class);
        if ($identity === null) {
            return $this->redirectToForbidden();
        }

        if ($this->hasRequiredAbility($identity->getRoles())) {
            return $handler->handle($request);
        }

        return $this->redirectToForbidden();
    }

    /**
     * @param string[] $roles
     */
    private function hasRequiredAbility(array $roles): bool
    {
        if ($this->requiredAbilities === []) {
            return true;
        }

        foreach ($this->requiredAbilities as $ability) {
            if ($this->policy->isGranted($roles, $ability)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $abilities
     * @return string[]
     */
    private function normalizeAbilities(array $abilities): array
    {
        $normalized = [];
        foreach ($abilities as $ability) {
            if (!is_string($ability)) {
                continue;
            }

            $ability = strtolower(trim($ability));
            if ($ability !== '') {
                $normalized[] = $ability;
            }
        }

        return $normalized;
    }

    private function redirectToForbidden(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(302);

        return $response->withHeader('Location', $this->forbiddenRoute);
    }
}
