<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\RefreshTokenId;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class RefreshToken
{
    private RefreshTokenId $id;
    private UserId $userId;
    private TokenHash $token;
    private TokenFamily $family;
    private DateTimeImmutable $expiresAt;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $usedAt;

    public function __construct(
        RefreshTokenId     $id,
        UserId             $userId,
        TokenHash          $token,
        TokenFamily        $family,
        DateTimeImmutable  $expiresAt,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $usedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->token = $token;
        $this->family = $family;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->usedAt = $usedAt;
    }

    public static function create(
        RefreshTokenId $id,
        UserId $userId,
        TokenHash $token,
        TokenFamily $family,
        DateTimeImmutable $expiresAt
    ): self {
        return new self($id, $userId, $token, $family, $expiresAt);
    }

    public function isValid(): bool
    {
        return $this->usedAt === null
            && $this->expiresAt > new DateTimeImmutable();
    }

    public function markAsUsed(): void
    {
        if ($this->usedAt !== null) {
            throw new \DomainException('Token already used');
        }
        $this->usedAt = new DateTimeImmutable();
    }

    public function belongsToFamily(TokenFamily $family): bool
    {
        return $this->family->equals($family);
    }


    public function getId(): RefreshTokenId { return $this->id; }
    public function getUserId(): UserId { return $this->userId; }
    public function getToken(): TokenHash { return $this->token; }
    public function getFamily(): TokenFamily { return $this->family; }
    public function getExpiresAt(): DateTimeImmutable { return $this->expiresAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUsedAt(): ?DateTimeImmutable { return $this->usedAt; }
}
