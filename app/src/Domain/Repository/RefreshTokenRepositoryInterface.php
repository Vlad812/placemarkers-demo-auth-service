<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\RefreshToken;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;

interface RefreshTokenRepositoryInterface
{
    public function findByToken(TokenHash $token): ?RefreshToken;
    public function findByFamily(TokenFamily $family): array;
    public function save(RefreshToken $token): void;
    public function revokeFamily(TokenFamily $family): void;
    public function revokeAllUserTokens(UserId $userId): void;
    public function deleteExpiredTokens(): void;
}
