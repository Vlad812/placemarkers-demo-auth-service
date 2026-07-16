<?php

declare(strict_types=1);

namespace App\Application\Command;

use Webmozart\Assert\Assert;

final readonly class LogoutCommand
{
    public function __construct(
        public string $userId,
        public ?string $refreshToken = null,
    ) {
    }

    /**
     * @param array $requestData
     * @param string $userId
     * @return self
     */
    public static function createFromRawValues(array $requestData, string $userId): self
    {
        Assert::notEmpty($userId, 'User id is required for logout.');

        $refreshToken = $requestData['refresh_token'] ?? null;

        return new self($userId, $refreshToken);
    }
}
