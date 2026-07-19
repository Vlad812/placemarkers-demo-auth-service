<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Security;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Security\SecurityUserAdapter;
use PHPUnit\Framework\TestCase;

final class SecurityUserAdapterTest extends TestCase
{
    public function testGetUserIdentifierReturnsUserUuid(): void
    {
        $userId = UserId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $user = new User(
            $userId,
            new Email('user@example.com'),
            new PasswordHash(str_repeat('a', 60)),
            Role::user()
        );

        $adapter = new SecurityUserAdapter($user);

        self::assertSame($userId->getValue(), $adapter->getUserIdentifier());
    }
}
