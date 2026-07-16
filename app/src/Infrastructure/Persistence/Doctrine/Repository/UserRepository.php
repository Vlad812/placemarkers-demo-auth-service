<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Entity\UserOrmEntity;
use App\Infrastructure\Persistence\Doctrine\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserMapper             $mapper
    ) {
    }

    public function findById(UserId $id): ?User
    {
        $ormEntity = $this->entityManager
            ->getRepository(UserOrmEntity::class)
            ->find($id->getValue());

        return $ormEntity ? $this->mapper->toDomain($ormEntity) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $ormEntity = $this->entityManager
            ->getRepository(UserOrmEntity::class)
            ->findOneBy(['email' => $email->getValue()]);

        return $ormEntity ? $this->mapper->toDomain($ormEntity) : null;
    }

    public function findByEmailVerificationToken(string $token): ?User
    {
        $ormEntity = $this->entityManager
            ->getRepository(UserOrmEntity::class)
            ->findOneBy(['emailVerificationToken' => $token]);

        return $ormEntity ? $this->mapper->toDomain($ormEntity) : null;
    }

    public function findByPasswordResetToken(string $token): ?User
    {
        $ormEntity = $this->entityManager
            ->getRepository(UserOrmEntity::class)
            ->findOneBy(['passwordResetToken' => $token]);

        return $ormEntity ? $this->mapper->toDomain($ormEntity) : null;
    }

    public function save(User $user): void
    {
        $existing = $this->entityManager
            ->getRepository(UserOrmEntity::class)
            ->find($user->getId()->getValue());

        if ($existing) {
            $this->mapper->updateOrmEntity($existing, $user);
        } else {
            $ormEntity = $this->mapper->toOrmEntity($user);
            $this->entityManager->persist($ormEntity);
        }

        $this->entityManager->flush();
    }

    public function delete(UserId $id): void
    {
        $ormEntity = $this->entityManager
            ->getRepository(UserOrmEntity::class)
            ->find($id->getValue());

        if ($ormEntity) {
            $this->entityManager->remove($ormEntity);
            $this->entityManager->flush();
        }
    }
}


