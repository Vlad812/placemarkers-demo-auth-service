<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\Email;
use Webmozart\Assert\Assert;

final readonly class RequestPasswordResetCommand
{
    public function __construct(
        public Email $email,
    ) {
    }

    public static function createFromRawValues(array $requestData): self
    {
        Assert::keyExists($requestData, 'email');

        $email = new Email($requestData['email']);

        return new self($email);
    }
}
