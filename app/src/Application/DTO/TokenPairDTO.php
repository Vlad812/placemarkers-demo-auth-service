<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class TokenPairDTO
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly int $expiresIn,
        public readonly int $refreshExpiresIn,
        public readonly string $tokenType = 'Bearer'
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'expires_in' => $this->expiresIn,
            'token_type' => $this->tokenType,
        ];
    }
}
