<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\RefreshToken;
use App\Domain\ValueObject\RefreshTokenId;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

final class RefreshTokenTest extends TestCase
{
    private RefreshTokenId $tokenId;
    private UserId $userId;
    private TokenHash $tokenHash;
    private TokenFamily $tokenFamily;

    protected function setUp(): void
    {
        $this->tokenId = RefreshTokenId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $this->userId = UserId::fromString('123e4567-e89b-12d3-a456-426614174001');
        $this->tokenHash = new TokenHash('some-hash-value');
        $this->tokenFamily = TokenFamily::fromString('family-1');
    }

    public function testCreateRefreshToken(): void
    {
        $expiresAt = new DateTimeImmutable('+1 hour');
        
        $refreshToken = RefreshToken::create(
            $this->tokenId,
            $this->userId,
            $this->tokenHash,
            $this->tokenFamily,
            $expiresAt
        );

        $this->assertSame($this->tokenId, $refreshToken->getId());
        $this->assertSame($this->userId, $refreshToken->getUserId());
        $this->assertSame($this->tokenHash, $refreshToken->getToken());
        $this->assertSame($this->tokenFamily, $refreshToken->getFamily());
        $this->assertSame($expiresAt, $refreshToken->getExpiresAt());
        $this->assertNull($refreshToken->getUsedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $refreshToken->getCreatedAt());
        $this->assertTrue($refreshToken->isValid());
    }

    public function testIsValidReturnsFalseIfExpired(): void
    {
        $expiresAt = new DateTimeImmutable('-1 hour');
        
        $refreshToken = RefreshToken::create(
            $this->tokenId,
            $this->userId,
            $this->tokenHash,
            $this->tokenFamily,
            $expiresAt
        );

        $this->assertFalse($refreshToken->isValid());
    }

    public function testMarkAsUsed(): void
    {
        $expiresAt = new DateTimeImmutable('+1 hour');
        
        $refreshToken = RefreshToken::create(
            $this->tokenId,
            $this->userId,
            $this->tokenHash,
            $this->tokenFamily,
            $expiresAt
        );

        $this->assertTrue($refreshToken->isValid());
        
        $refreshToken->markAsUsed();
        
        $this->assertInstanceOf(DateTimeImmutable::class, $refreshToken->getUsedAt());
        $this->assertFalse($refreshToken->isValid());
    }

    public function testMarkAsUsedThrowsExceptionIfAlreadyUsed(): void
    {
        $expiresAt = new DateTimeImmutable('+1 hour');
        
        $refreshToken = RefreshToken::create(
            $this->tokenId,
            $this->userId,
            $this->tokenHash,
            $this->tokenFamily,
            $expiresAt
        );

        $refreshToken->markAsUsed();
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Token already used');
        
        $refreshToken->markAsUsed();
    }

    public function testBelongsToFamily(): void
    {
        $expiresAt = new DateTimeImmutable('+1 hour');
        
        $refreshToken = RefreshToken::create(
            $this->tokenId,
            $this->userId,
            $this->tokenHash,
            $this->tokenFamily,
            $expiresAt
        );

        $sameFamily = TokenFamily::fromString('family-1');
        $differentFamily = TokenFamily::fromString('family-2');
        
        $this->assertTrue($refreshToken->belongsToFamily($sameFamily));
        $this->assertFalse($refreshToken->belongsToFamily($differentFamily));
    }
}
