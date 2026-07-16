<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\ValueObject\Email;
use Webmozart\Assert\Assert;

final readonly class SignupCommand
{
    public function __construct(
        public Email  $email,
        public string $password,
    ) {
    }

    /**
     * @param array $requestData
     * @return self
     */
    public static function createFromRawValues(array $requestData): self
    {
        Assert::keyExists($requestData, 'email');
        Assert::keyExists($requestData, 'password');

        $email = $requestData['email'];
        $password = $requestData['password'];

        $email = new Email($email);

        return new self($email, $password);
    }
}
