<?php

declare(strict_types=1);

namespace Tests\Domain\Auth;

use App\Domain\Shared\DomainException;
use App\Domain\Token\TokenClaims;
use App\Domain\Token\TokenEncoder;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TokenEncoderTest extends TestCase
{
    public function testEncodeAndDecodeRoundTripWithDefaultAlgorithm(): void
    {
        $encoder = new TokenEncoder('test-secret');
        $claims = $this->createClaims();

        $token = $encoder->encode($claims);
        $decoded = $encoder->decode($token->getToken());

        self::assertSame($claims->toArray(), $decoded->toArray());
    }

    public function testConstructorNormalizesEmptyAlgorithmToDefault(): void
    {
        $claims = $this->createClaims();

        $encoderWithWhitespace = new TokenEncoder('test-secret', '   ');
        $encoderWithDefault = new TokenEncoder('test-secret', 'sha256');

        $tokenWithWhitespace = $encoderWithWhitespace->encode($claims);
        $tokenWithDefault = $encoderWithDefault->encode($claims);

        self::assertSame($tokenWithDefault->getToken(), $tokenWithWhitespace->getToken());
    }

    public function testConstructorThrowsForUnsupportedAlgorithm(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Unsupported hashing algorithm "unsupported-algo".');

        new TokenEncoder('test-secret', 'unsupported-algo');
    }

    private function createClaims(): TokenClaims
    {
        $issuedAt = new DateTimeImmutable('2024-01-01T00:00:00+00:00');
        $expiresAt = $issuedAt->add(new DateInterval('PT1H'));

        return new TokenClaims('user-123', 'user@example.com', ['ROLE_USER'], $issuedAt, $expiresAt);
    }
}
