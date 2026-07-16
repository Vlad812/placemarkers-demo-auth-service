<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Service\TokenServiceInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\RefreshTokenId;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Infrastructure\Security\SecurityUserAdapter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;

final class LexikTokenService implements TokenServiceInterface
{
    private int $refreshTokenTtl;

    public function __construct(
        private readonly JWTTokenManagerInterface        $jwtManager,
        private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
        private readonly UuidGeneratorInterface          $uuidGenerator,
        int $refreshTokenTtl = 2592000
    ) {
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    public function createAccessToken(User $user): string
    {
        $securityUser = new SecurityUserAdapter($user);

        return $this->jwtManager->create($securityUser);
    }

    /**
     * @throws RandomException
     */
    public function createRefreshToken(User $user, TokenFamily $family): string
    {
        $tokenValue = TokenHash::generate()->getValue();

        $refreshToken = RefreshToken::create(
            RefreshTokenId::fromString($this->uuidGenerator->generate()),
            $user->getId(),
            new TokenHash($tokenValue),
            $family,
            new \DateTimeImmutable("+{$this->refreshTokenTtl} seconds")
        );

        $this->refreshTokenRepository->save($refreshToken);

        return $tokenValue;
    }

    public function decodeAccessToken(string $token): array
    {
        return $this->jwtManager->decode($this->jwtManager->parse($token));
    }

    public function generateTokenFamily(): TokenFamily
    {
        return TokenFamily::fromString($this->uuidGenerator->generate());
    }
}
