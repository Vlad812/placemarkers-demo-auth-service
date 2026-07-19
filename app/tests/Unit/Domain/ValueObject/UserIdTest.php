<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function testValidUuid(): void
    {
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $userId = UserId::fromString($uuid);
        
        $this->assertSame($uuid, $userId->getValue());
    }

    public function testInvalidUuidThrowsException(): void
    {
        $invalidUuid = 'invalid-uuid';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('"%s" is not a valid UUID.', $invalidUuid));
        
        UserId::fromString($invalidUuid);
    }

    public function testEqualsReturnsTrueForSameUuid(): void
    {
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $userId1 = UserId::fromString($uuid);
        $userId2 = UserId::fromString(strtoupper($uuid)); // Test case insensitivity
        
        $this->assertTrue($userId1->equals($userId2));
    }

    public function testEqualsReturnsFalseForDifferentUuid(): void
    {
        $userId1 = UserId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $userId2 = UserId::fromString('123e4567-e89b-12d3-a456-426614174001');
        
        $this->assertFalse($userId1->equals($userId2));
    }
}
