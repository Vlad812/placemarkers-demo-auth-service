<?php

declare(strict_types=1);

namespace App\Application\Command;

use Webmozart\Assert\Assert;

final readonly class ResetPasswordCommand
{
    public function __construct(
        public string $token,
        public string $password,
    ) {
    }

    public static function createFromRawValues(array $requestData): self
    {
        Assert::keyExists($requestData, 'token');
        Assert::keyExists($requestData, 'password');
        Assert::stringNotEmpty($requestData['token']);
        Assert::stringNotEmpty($requestData['password']);

        return new self($requestData['token'], $requestData['password']);
    }
}
