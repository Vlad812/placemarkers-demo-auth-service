<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\RefreshTokenCommand;
use App\Application\DTO\TokenPairDTO;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\TokenServiceInterface;
use App\Domain\ValueObject\TokenHash;

final class RefreshTokenHandler
{
    public function __construct(
        private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenServiceInterface $tokenService,
        private readonly int $accessTokenTtl = 3600,
        private readonly int $refreshTokenTtl = 2592000 // 30 days default
    ) {}

    public function __invoke(RefreshTokenCommand $command): TokenPairDTO
    {
        return $this->handle($command);
    }

    /**
     * @throws AuthenticationException
     */
    public function handle(RefreshTokenCommand $command): TokenPairDTO
    {
        $tokenHash = new TokenHash($command->refreshToken);
        $refreshToken = $this->refreshTokenRepository->findByToken($tokenHash);

        if ($refreshToken === null) {
            throw AuthenticationException::tokenInvalid();
        }

        // Проверка на reuse (возможная кража токена)
        if (!$refreshToken->isValid()) {
            // Отзываем все токены семейства
            $this->refreshTokenRepository->revokeFamily($refreshToken->getFamily());
            throw AuthenticationException::tokenReused();
        }

        $user = $this->userRepository->findById($refreshToken->getUserId());

        if ($user === null || !$user->isActive()) {
            throw AuthenticationException::tokenInvalid();
        }

        // Помечаем старый токен как использованный (rotation)
        $refreshToken->markAsUsed();
        $this->refreshTokenRepository->save($refreshToken);

        // Создаем новую пару токенов в том же семействе (sliding session)
        $accessToken = $this->tokenService->createAccessToken($user);
        $newRefreshToken = $this->tokenService->createRefreshToken(
            $user,
            $refreshToken->getFamily()
        );

        return new TokenPairDTO(
            $accessToken,
            $newRefreshToken,
            $this->accessTokenTtl,
            $this->refreshTokenTtl
        );
    }
}
