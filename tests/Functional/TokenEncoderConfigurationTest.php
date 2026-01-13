<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Domain\Auth\TokenClaims;
use App\Domain\Auth\TokenEncoder;
use App\Domain\Shared\DomainException;
use DateInterval;
use DateTimeImmutable;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

final class TokenEncoderConfigurationTest extends TestCase
{
    /** @var array<string, array{set: bool, value: ?string}> */
    private array $envBackup = [];

    /** @var array<string, array{set: bool, value: ?string}> */
    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['TOKEN_SECRET', 'TOKEN_ALGORITHM'] as $key) {
            $this->p[$key] = [
                'set' => array_key_exists($key, $_ENV),
                'value' => $_ENV[$key] ?? null,
            ];
            $this->serverBackup[$key] = [
                'set' => array_key_exists($key, $_SERVER),
                'value' => $_SERVER[$key] ?? null,
            ];
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->envBackup as $key => $data) {
            if ($data['set']) {
                $_ENV[$key] = $data['value'];
            } else {
                unset($_ENV[$key]);
            }
        }

        foreach ($this->serverBackup as $key => $data) {
            if ($data['set']) {
                $_SERVER[$key] = $data['value'];
            } else {
                unset($_SERVER[$key]);
            }
        }
        parent::tearDown();
    }

    public function testContainerBuildsTokenEncoderUsingCustomAlgorithm(): void
    {
        $_ENV['TOKEN_SECRET'] = 'functional-secret';
        unset($_SERVER['TOKEN_SECRET']);
        $_ENV['TOKEN_ALGORITHM'] = 'sha512';
        unset($_SERVER['TOKEN_ALGORITHM']);

        $container = $this->buildContainer();

        $encoder = $container->get(TokenEncoder::class);
        $claims = $this->createClaims();

        $expected = (new TokenEncoder('functional-secret', 'sha512'))->encode($claims);
        $actual = $encoder->encode($claims);

        self::assertSame($expected->getToken(), $actual->getToken());
    }

    public function testContainerThrowsWhenInvalidAlgorithmProvided(): void
    {
        $_ENV['TOKEN_SECRET'] = 'functional-secret';
        $_ENV['TOKEN_ALGORITHM'] = 'invalid-hmac';

        $container = $this->buildContainer();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Unsupported hashing algorithm "invalid-hmac".');

        $container->get(TokenEncoder::class);
    }

    private function buildContainer(): \Psr\Container\ContainerInterface
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(dirname(__DIR__, 2) . '/config/container.php');

        return $builder->build();
    }

    private function createClaims(): TokenClaims
    {
        $issuedAt = new DateTimeImmutable('2024-01-01T00:00:00+00:00');
        $expiresAt = $issuedAt->add(new DateInterval('PT1H'));

        return new TokenClaims('user-123', 'user@example.com', ['ROLE_USER'], $issuedAt, $expiresAt);
    }
}
