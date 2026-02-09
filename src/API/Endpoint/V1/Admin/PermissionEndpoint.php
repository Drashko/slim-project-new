<?php

declare(strict_types=1);

namespace App\API\Endpoint\V1\Admin;

use App\Domain\Permission\PermissionInterface;
use App\Domain\Permission\PermissionRepositoryInterface;
use App\Domain\Shared\DomainException;
use App\Feature\Admin\Permission\Command\CreatePermissionCommand;
use App\Feature\Admin\Permission\Command\ListPermissionsCommand;
use App\Feature\Admin\Permission\DtoPermissionRequest;
use App\Feature\Admin\Permission\Handler\CreatePermissionHandler;
use App\Feature\Admin\Permission\Handler\ListPermissionsHandler;
use App\Feature\Admin\Permission\ValidatePermissionRequest;
use App\Integration\Casbin\CasbinRule;
use App\Integration\Casbin\CasbinRuleRepository;
use App\Integration\Helper\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PermissionEndpoint
{
    use JsonResponseTrait;

    public function __construct(
        private readonly CreatePermissionHandler $createPermission,
        private readonly ListPermissionsHandler $listPermissions,
        private readonly ValidatePermissionRequest $validator,
        private readonly PermissionRepositoryInterface $permissions,
        private readonly CasbinRuleRepository $casbinRules,
    ) {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $input = new DtoPermissionRequest($request->getQueryParams());
        $errors = $this->validator->validate($input);

        if ($errors !== []) {
            return $this->respondWithJson($response, [
                'status' => 'error',
                'errors' => $errors,
            ], 422);
        }

        $result = $this->listPermissions->handle(new ListPermissionsCommand($input));
        $rules = array_map(
            static fn(CasbinRule $rule): array => [
                'ptype' => $rule->getPtype(),
                'values' => $rule->getValues(),
                'policy' => $rule->toPolicyLine(),
            ],
            $this->casbinRules->all()
        );

        return $this->respondWithJson($response, [
            'status' => 'ok',
            'permissions' => [
                'groups' => array_map(
                    static fn($group): array => [
                        'id' => $group->getId(),
                        'label' => $group->getLabel(),
                        'description' => $group->getDescription(),
                        'permissions' => array_map(
                            static fn($permission): array => [
                                'key' => $permission->getKey(),
                                'label' => $permission->getLabel(),
                            ],
                            $group->getPermissions()
                        ),
                    ],
                    $result->getGroups()
                ),
                'granted' => $result->getGranted(),
                'total' => $result->getTotalPermissions(),
            ],
            'casbin_rules' => $rules,
        ]);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $payload = $this->parseJsonBody($request);

        $key = trim((string) ($payload['key'] ?? ''));
        $label = trim((string) ($payload['label'] ?? ''));
        $subject = trim((string) ($payload['subject'] ?? ''));
        $object = trim((string) ($payload['object'] ?? ''));
        $action = strtoupper(trim((string) ($payload['action'] ?? '')));
        $scope = trim((string) ($payload['scope'] ?? 'api'));
        $ptype = trim((string) ($payload['ptype'] ?? 'p'));

        $errors = $this->validateCreatePayload($key, $label, $subject, $object, $action, $scope, $ptype);
        if ($errors !== []) {
            return $this->respondWithJson($response, [
                'status' => 'error',
                'errors' => $errors,
            ], 422);
        }

        $permission = $this->permissions->findByKey($key);

        if (!$permission instanceof PermissionInterface) {
            try {
                $permission = $this->createPermission->handle(new CreatePermissionCommand($key, $label));
            } catch (DomainException $exception) {
                return $this->respondWithJson($response, [
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ], 409);
            }
        }

        $values = [$subject, $object, $action, $scope];
        $ruleExists = false;
        foreach ($this->casbinRules->all() as $rule) {
            if ($rule->matchesPolicy($ptype, $values)) {
                $ruleExists = true;
                break;
            }
        }

        if (!$ruleExists) {
            $this->casbinRules->add($ptype, $values);
            $this->casbinRules->flush();
        }

        return $this->respondWithJson($response, [
            'status' => 'ok',
            'permission' => [
                'key' => $permission->getKey(),
                'label' => $permission->getLabel(),
            ],
            'casbin_rule' => [
                'ptype' => $ptype,
                'values' => $values,
            ],
            'rule_created' => !$ruleExists,
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJsonBody(ServerRequestInterface $request): array
    {
        $raw = (string) $request->getBody();
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @return array<string, string>
     */
    private function validateCreatePayload(
        string $key,
        string $label,
        string $subject,
        string $object,
        string $action,
        string $scope,
        string $ptype
    ): array {
        $errors = [];

        if ($key === '') {
            $errors['key'] = 'Permission key is required.';
        }

        if ($label === '') {
            $errors['label'] = 'Permission label is required.';
        }

        if ($subject === '') {
            $errors['subject'] = 'Policy subject is required.';
        }

        if ($object === '') {
            $errors['object'] = 'Policy object is required.';
        }

        if ($action === '') {
            $errors['action'] = 'Policy action is required.';
        }

        if ($scope === '') {
            $errors['scope'] = 'Policy scope is required.';
        }

        if ($ptype === '') {
            $errors['ptype'] = 'Policy type is required.';
        }

        return $errors;
    }
}
