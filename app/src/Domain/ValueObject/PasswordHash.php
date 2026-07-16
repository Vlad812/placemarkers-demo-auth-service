<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class PasswordHash
{
    private const int PASSWORD_LENGTH = 8;

    private string $value;

    public function __construct(string $value)
    {
        if (strlen($value) < 60) { // bcrypt hash length check
            throw new \InvalidArgumentException('Invalid password hash');
        }
        $this->value = $value;
    }

    public static function fromPlainPassword(string $plainPassword, callable $hashCallback): self
    {
        if (strlen($plainPassword) < self::PASSWORD_LENGTH) {
            throw new \InvalidArgumentException('Password too short');
        }
        return new self(($hashCallback)($plainPassword));
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
