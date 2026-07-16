<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\LogoutCommand;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;

final readonly class LogoutHandler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {}

    public function __invoke(LogoutCommand $command): void
    {
        $userId = UserId::fromString($command->userId);

        if ($command->refreshToken !== null) {
            // Logout с одного устройства - отзываем семейство конкретного токена
            $token = $this->refreshTokenRepository->findByToken(
                new TokenHash($command->refreshToken)
            );

            if ($token !== null) {
                $this->refreshTokenRepository->revokeFamily($token->getFamily());
            }
        } else {
            // Logout со всех устройств
            $this->refreshTokenRepository->revokeAllUserTokens($userId);
        }
    }
}
