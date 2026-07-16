<?php

declare(strict_types=1);

namespace App\Application\Command;

use Webmozart\Assert\Assert;

final readonly class ConfirmEmailCommand
{
    public function __construct(
        public string $token,
    ) {
    }

    public static function createFromRawValues(array $requestData): self
    {
        Assert::keyExists($requestData, 'token');
        Assert::stringNotEmpty($requestData['token']);

        return new self($requestData['token']);
    }
}
