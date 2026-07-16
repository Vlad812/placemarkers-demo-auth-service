<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Entity\RefreshToken;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Entity\RefreshTokenOrmEntity;
use App\Infrastructure\Persistence\Doctrine\Mapper\RefreshTokenMapper;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RefreshTokenMapper     $mapper
    ) {}

    public function findByToken(TokenHash $token): ?RefreshToken
    {
        $ormEntity = $this->entityManager
            ->getRepository(RefreshTokenOrmEntity::class)
            ->findOneBy(['token' => $token->getValue()]);

        return $ormEntity ? $this->mapper->toDomain($ormEntity) : null;
    }

    public function findByFamily(TokenFamily $family): array
    {
        $ormEntities = $this->entityManager
            ->getRepository(RefreshTokenOrmEntity::class)
            ->findBy(['family' => $family->getValue()]);

        return array_map(
            fn($entity) => $this->mapper->toDomain($entity),
            $ormEntities
        );
    }

    public function save(RefreshToken $token): void
    {
        $existing = $this->entityManager
            ->getRepository(RefreshTokenOrmEntity::class)
            ->find($token->getId()->getValue());

        if ($existing) {
            $this->mapper->updateOrmEntity($existing, $token);
        } else {
            $ormEntity = $this->mapper->toOrmEntity($token);
            $this->entityManager->persist($ormEntity);
        }

        $this->entityManager->flush();
    }

    public function revokeFamily(TokenFamily $family): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(RefreshTokenOrmEntity::class, 't')
            ->set('t.usedAt', ':now')
            ->where('t.family = :family')
            ->andWhere('t.usedAt IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('family', $family->getValue())
            ->getQuery()
            ->execute();
    }

    public function revokeAllUserTokens(UserId $userId): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(RefreshTokenOrmEntity::class, 't')
            ->set('t.usedAt', ':now')
            ->where('t.userId = :userId')
            ->andWhere('t.usedAt IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('userId', $userId->getValue())
            ->getQuery()
            ->execute();
    }

    public function deleteExpiredTokens(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(RefreshTokenOrmEntity::class, 't')
            ->where('t.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
