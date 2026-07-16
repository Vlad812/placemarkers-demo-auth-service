<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class Role
{
    public const string ADMIN = 'admin';
    public const string USER = 'user';

    private string $value;

    public function __construct(string $value)
    {
        if (!in_array($value, [self::ADMIN, self::USER], true)) {
            throw new \InvalidArgumentException('Invalid role');
        }
        $this->value = $value;
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function user(): self
    {
        return new self(self::USER);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }
}
