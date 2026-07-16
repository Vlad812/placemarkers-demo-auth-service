<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Random\RandomException;

final class TokenHash
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws RandomException
     */
    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(string $plainToken): bool
    {
        return hash_equals($this->value, $plainToken);
    }
}
