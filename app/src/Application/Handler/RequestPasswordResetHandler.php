<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\RequestPasswordResetCommand;
use App\Application\Message\PasswordResetRequestedMessage;
use App\Domain\Repository\UserRepositoryInterface;
use Random\RandomException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class RequestPasswordResetHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Returns true when the email exists and a reset message was queued.
     *
     * @param RequestPasswordResetCommand $command
     * @return bool
     * @throws ExceptionInterface
     * @throws RandomException
     */
    public function __invoke(RequestPasswordResetCommand $command): bool
    {
        $user = $this->userRepository->findByEmail($command->email);

        if ($user === null) {
            return false;
        }

        $resetToken = bin2hex(random_bytes(32));
        $user->issuePasswordResetToken($resetToken);
        $this->userRepository->save($user);

        $this->messageBus->dispatch(new PasswordResetRequestedMessage(
            $user->getEmail()->getValue(),
            $resetToken,
        ));

        return true;
    }
}
