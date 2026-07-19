<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\ConfirmEmailCommand;
use App\Application\Handler\ConfirmEmailHandler;
use App\Application\Message\GreetingEmailMessage;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ConfirmEmailHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    }

    public function testSuccessfulEmailConfirmation(): void
    {
        $token = 'valid-token';
        $command = new ConfirmEmailCommand($token);

        $userId = UserId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $email = new Email('test@example.com');
        $user = User::create(
            $userId,
            $email,
            new PasswordHash(str_repeat('a', 60)),
            Role::user()
        );
        $user->issueEmailVerificationToken($token);

        $this->userRepository->expects($this->once())
            ->method('findByEmailVerificationToken')
            ->with($token)
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use ($userId, $email) {
                return $message instanceof GreetingEmailMessage
                    && $message->userId === $userId->getValue()
                    && $message->email === $email->getValue();
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $handler = new ConfirmEmailHandler($this->userRepository, $messageBus);
        ($handler)($command);

        $this->assertTrue($user->isActive());
        $this->assertNull($user->getEmailVerificationToken());
    }

    public function testEmailConfirmationFailsIfTokenInvalid(): void
    {
        $token = 'invalid-token';
        $command = new ConfirmEmailCommand($token);

        $messageBus = $this->createStub(MessageBusInterface::class);
        $handler = new ConfirmEmailHandler($this->userRepository, $messageBus);

        $this->userRepository->expects($this->once())
            ->method('findByEmailVerificationToken')
            ->with($token)
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Email confirmation token is invalid');

        ($handler)($command);
    }
}
