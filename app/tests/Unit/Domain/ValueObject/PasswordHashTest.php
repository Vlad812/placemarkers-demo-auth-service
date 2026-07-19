<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\PasswordHash;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PasswordHashTest extends TestCase
{
    public function testValidHash(): void
    {
        $validHash = str_repeat('a', 60);
        $passwordHash = new PasswordHash($validHash);
        
        $this->assertSame($validHash, $passwordHash->getValue());
    }

    public function testShortHashThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid password hash');
        
        new PasswordHash(str_repeat('a', 59));
    }

    public function testFromPlainPasswordWithValidPassword(): void
    {
        $plainPassword = 'password123';
        $mockHashCallback = function (string $plain) {
            return str_repeat($plain, 6); // Just to make it >= 60 chars
        };
        
        $passwordHash = PasswordHash::fromPlainPassword($plainPassword, $mockHashCallback);
        $this->assertSame($mockHashCallback($plainPassword), $passwordHash->getValue());
    }

    public function testFromPlainPasswordWithShortPasswordThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password too short');
        
        $mockHashCallback = function (string $plain) {
            return str_repeat('a', 60);
        };
        
        PasswordHash::fromPlainPassword('short', $mockHashCallback);
    }
}
