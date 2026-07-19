<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\RefreshTokenId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RefreshTokenIdTest extends TestCase
{
    public function testValidUuid(): void
    {
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $tokenId = RefreshTokenId::fromString($uuid);
        
        $this->assertSame($uuid, $tokenId->getValue());
    }

    public function testInvalidUuidThrowsException(): void
    {
        $invalidUuid = 'invalid-uuid';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('"%s" is not a valid UUID.', $invalidUuid));
        
        RefreshTokenId::fromString($invalidUuid);
    }

    public function testEqualsReturnsTrueForSameUuid(): void
    {
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $tokenId1 = RefreshTokenId::fromString($uuid);
        $tokenId2 = RefreshTokenId::fromString(strtoupper($uuid)); // Test case insensitivity
        
        $this->assertTrue($tokenId1->equals($tokenId2));
    }

    public function testEqualsReturnsFalseForDifferentUuid(): void
    {
        $tokenId1 = RefreshTokenId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $tokenId2 = RefreshTokenId::fromString('123e4567-e89b-12d3-a456-426614174001');
        
        $this->assertFalse($tokenId1->equals($tokenId2));
    }
}
