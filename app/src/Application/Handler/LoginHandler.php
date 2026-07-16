<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\LoginCommand;
use App\Application\DTO\TokenPairDTO;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\TokenServiceInterface;

final readonly class LoginHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenServiceInterface   $tokenService,
        private int                     $accessTokenTtl = 3600, // 1 hour !!!
        private int                     $refreshTokenTtl = 2592000, // 30 days default
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    public function __invoke(LoginCommand $command): TokenPairDTO
    {
        $user = $this->userRepository->findByEmail($command->email);

        if ($user === null) {
            throw AuthenticationException::invalidCredentials();
        }

        if (!$user->isActive()) {
            throw AuthenticationException::userInactive();
        }

        if (!$user->verifyPassword(
            $command->password,
            fn (string $plain, string $hash) => password_verify($plain, $hash)
        )) {
            throw AuthenticationException::invalidCredentials();
        }

        $family = $this->tokenService->generateTokenFamily();

        $accessToken = $this->tokenService->createAccessToken($user);
        $refreshToken = $this->tokenService->createRefreshToken($user, $family);

        return new TokenPairDTO(
            $accessToken,
            $refreshToken,
            $this->accessTokenTtl,
            $this->refreshTokenTtl,
        );
    }
}
