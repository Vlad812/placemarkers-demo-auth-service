<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\SignupCommand;
use App\Application\Message\UserRegisteredMessage;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use Random\RandomException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class SignupHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UuidGeneratorInterface  $uuidGenerator,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param SignupCommand $command
     * @return User
     * @throws AuthenticationException
     * @throws RandomException
     * @throws ExceptionInterface
     */
    public function __invoke(SignupCommand $command): User
    {
        $existing = $this->userRepository->findByEmail($command->email);

        if ($existing !== null) {
            throw AuthenticationException::userAlreadyExists();
        }

        $passwordHash = PasswordHash::fromPlainPassword(
            $command->password,
            fn (string $plain) => password_hash($plain, PASSWORD_BCRYPT)
        );

        $user = User::create(
            UserId::fromString($this->uuidGenerator->generate()),
            $command->email,
            $passwordHash,
            Role::user(),
        );

        $confirmationToken = bin2hex(random_bytes(32));
        $user->issueEmailVerificationToken($confirmationToken);

        $this->userRepository->save($user);

        $this->messageBus->dispatch(new UserRegisteredMessage(
            $user->getId()->getValue(),
            $user->getEmail()->getValue(),
            $confirmationToken,
        ));

        return $user;
    }
}
