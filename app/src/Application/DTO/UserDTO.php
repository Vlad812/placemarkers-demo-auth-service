<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class UserDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $role,
        public readonly bool $isActive
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['email'],
            $data['role'],
            $data['isActive']
        );
    }
}
