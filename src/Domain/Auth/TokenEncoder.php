<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Shared\DomainException;
use JsonException;

final readonly class TokenEncoder
{
    private string $secret;

    private string $algorithm;

    public function __construct(string $secret, string $algorithm = 'sha256')
    {
        $normalizedAlgorithm = trim($algorithm);
        if ($normalizedAlgorithm === '') {
            $normalizedAlgorithm = 'sha256';
        }

        if (!in_array($normalizedAlgorithm, hash_hmac_algos(), true)) {
            throw new DomainException(sprintf('Unsupported hashing algorithm "%s".', $normalizedAlgorithm));
        }

        $this->secret = $secret;
        $this->algorithm = $normalizedAlgorithm;
    }

    public function encode(TokenClaims $claims): AccessToken
    {
        $payload = $this->base64UrlEncode($this->jsonEncode($claims->toArray()));
        $signature = $this->base64UrlEncode($this->sign($payload));
        $token = sprintf('%s.%s', $payload, $signature);

        return new AccessToken($token, $claims->getExpiresAt());
    }

    public function decode(string $token): TokenClaims
    {
        [$payload, $signature] = $this->splitToken($token);
        $expected = $this->base64UrlEncode($this->sign($payload));

        if (!hash_equals($expected, $signature)) {
            throw new DomainException('Invalid token signature.');
        }

        $data = $this->jsonDecode($this->base64UrlDecode($payload));

        return TokenClaims::fromArray($data);
    }

    private function sign(string $payload): string
    {
        return hash_hmac($this->algorithm, $payload, $this->secret, true);
    }

    private function splitToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new DomainException('Malformed token provided.');
        }

        return $parts;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $decoded = base64_decode(strtr($data, '-_', '+/'), true);
        if ($decoded === false) {
            throw new DomainException('Unable to decode token payload.');
        }

        return $decoded;
    }

    /**
     * @throws DomainException
     */
    private function jsonEncode(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new DomainException('Unable to encode token payload: ' . $exception->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonDecode(string $json): array
    {
        try {
            /** @var array<string, mixed> $data */
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new DomainException('Unable to decode token payload: ' . $exception->getMessage());
        }

        return $data;
    }
}
