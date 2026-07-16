<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Exception\AuthenticationException;
use Webmozart\Assert\Assert;

final readonly class RefreshTokenCommand
{
    public function __construct(
        public string $refreshToken,
    ) {
    }

    /**
     * @param array $requestData
     * @return self
     * @throws AuthenticationException
     */
    public static function createFromRawValues(array $requestData): self
    {
        Assert::keyExists($requestData, 'refresh_token');

        $refreshToken = $requestData['refresh_token'];
        if ($refreshToken === null || '' === $refreshToken) {
            throw AuthenticationException::tokenInvalid();
        }

        return new self((string) $refreshToken);
    }
}
