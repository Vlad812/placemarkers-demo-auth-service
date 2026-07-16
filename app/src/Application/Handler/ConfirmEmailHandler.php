<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\ConfirmEmailCommand;
use App\Application\Message\GreetingEmailMessage;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ConfirmEmailHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws ExceptionInterface
     */
    public function __invoke(ConfirmEmailCommand $command): void
    {
        $user = $this->userRepository->findByEmailVerificationToken($command->token);

        if ($user === null) {
            throw AuthenticationException::emailConfirmationInvalid();
        }

        $user->confirmEmail();
        $this->userRepository->save($user);
        $this->messageBus->dispatch(new GreetingEmailMessage(
            $user->getId()->getValue(),
            $user->getEmail()->getValue(),
        ));
    }
}
