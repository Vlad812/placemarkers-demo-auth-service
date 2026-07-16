<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\ResetPasswordCommand;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\PasswordHash;

final readonly class ResetPasswordHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private string $passwordHashAlgo = PASSWORD_BCRYPT,
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    public function __invoke(ResetPasswordCommand $command): void
    {
        $user = $this->userRepository->findByPasswordResetToken($command->token);

        if ($user === null) {
            throw AuthenticationException::passwordResetInvalid();
        }

        $passwordHash = PasswordHash::fromPlainPassword(
            $command->password,
            fn (string $plain) => password_hash($plain, $this->passwordHashAlgo)
        );

        $user->updatePassword($passwordHash);
        $user->clearPasswordResetToken();
        $this->userRepository->save($user);
    }
}
