<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Entity\RefreshToken;
use App\Domain\ValueObject\RefreshTokenId;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Entity\RefreshTokenOrmEntity;

final class RefreshTokenMapper
{
    public function toDomain(RefreshTokenOrmEntity $ormEntity): RefreshToken
    {
        return new RefreshToken(
            RefreshTokenId::fromString($ormEntity->getId()),
            UserId::fromString($ormEntity->getUserId()),
            new TokenHash($ormEntity->getToken()),
            TokenFamily::fromString($ormEntity->getFamily()),
            $ormEntity->getExpiresAt(),
            $ormEntity->getCreatedAt(),
            $ormEntity->getUsedAt()
        );
    }

    public function toOrmEntity(RefreshToken $domainEntity): RefreshTokenOrmEntity
    {
        $ormEntity = new RefreshTokenOrmEntity();
        $ormEntity->setId($domainEntity->getId()->getValue());
        $ormEntity->setUserId($domainEntity->getUserId()->getValue());
        $ormEntity->setToken($domainEntity->getToken()->getValue());
        $ormEntity->setFamily($domainEntity->getFamily()->getValue());
        $ormEntity->setExpiresAt($domainEntity->getExpiresAt());
        $ormEntity->setCreatedAt($domainEntity->getCreatedAt());
        $ormEntity->setUsedAt($domainEntity->getUsedAt());

        return $ormEntity;
    }

    public function updateOrmEntity(RefreshTokenOrmEntity $ormEntity, RefreshToken $domainEntity): void
    {
        $ormEntity->setUsedAt($domainEntity->getUsedAt());
        // Остальные поллы неизменяемы по бизнес-логике
    }
}
