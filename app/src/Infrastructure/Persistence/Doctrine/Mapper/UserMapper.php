<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Mapper;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Entity\UserOrmEntity;
use App\Infrastructure\Persistence\Doctrine\Entity\UserRole;
use Symfony\Component\Uid\Uuid;

final class UserMapper
{
    public function toDomain(UserOrmEntity $ormEntity): User
    {
        return new User(
            UserId::fromString($ormEntity->getId()->toRfc4122()),
            new Email($ormEntity->getEmail()),
            new PasswordHash($ormEntity->getPasswordHash()),
            new Role($ormEntity->getRole()->value),
            $ormEntity->isActive(),
            $ormEntity->getEmailVerificationToken(),
            $ormEntity->getPasswordResetToken(),
            $ormEntity->getEmailVerifiedAt(),
            $ormEntity->getCreatedAt(),
            $ormEntity->getUpdatedAt()
        );
    }

    public function toOrmEntity(User $domainEntity): UserOrmEntity
    {
        $ormEntity = new UserOrmEntity();
        $ormEntity->setId(Uuid::fromString($domainEntity->getId()->getValue()));
        $ormEntity->setEmail($domainEntity->getEmail()->getValue());
        $ormEntity->setPasswordHash($domainEntity->getPasswordHash()->getValue());
        $ormEntity->setRole(UserRole::from($domainEntity->getRole()->getValue()));
        $ormEntity->setIsActive($domainEntity->isActive());
        $ormEntity->setEmailVerificationToken($domainEntity->getEmailVerificationToken());
        $ormEntity->setPasswordResetToken($domainEntity->getPasswordResetToken());
        $ormEntity->setEmailVerifiedAt($domainEntity->getEmailVerifiedAt());
        $ormEntity->setCreatedAt($domainEntity->getCreatedAt());
        $ormEntity->setUpdatedAt($domainEntity->getUpdatedAt());

        return $ormEntity;
    }

    public function updateOrmEntity(UserOrmEntity $ormEntity, User $domainEntity): void
    {
        $ormEntity->setEmail($domainEntity->getEmail()->getValue());
        $ormEntity->setPasswordHash($domainEntity->getPasswordHash()->getValue());
        $ormEntity->setRole(UserRole::from($domainEntity->getRole()->getValue()));
        $ormEntity->setIsActive($domainEntity->isActive());
        $ormEntity->setEmailVerificationToken($domainEntity->getEmailVerificationToken());
        $ormEntity->setPasswordResetToken($domainEntity->getPasswordResetToken());
        $ormEntity->setEmailVerifiedAt($domainEntity->getEmailVerifiedAt());
        $ormEntity->setUpdatedAt($domainEntity->getUpdatedAt());
    }
}
