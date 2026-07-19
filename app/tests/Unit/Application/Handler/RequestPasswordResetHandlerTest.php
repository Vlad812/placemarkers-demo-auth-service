<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\RequestPasswordResetCommand;
use App\Application\Handler\RequestPasswordResetHandler;
use App\Application\Message\PasswordResetRequestedMessage;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class RequestPasswordResetHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private MessageBusInterface&MockObject $messageBus;
    private RequestPasswordResetHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new RequestPasswordResetHandler(
            $this->userRepository,
            $this->messageBus
        );
    }

    public function testSuccessfulPasswordResetRequest(): void
    {
        $email = new Email('test@example.com');

        $command = new RequestPasswordResetCommand($email);

        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            $email,
            new PasswordHash(str_repeat('b', 60)),
            Role::user(),
            true
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use ($user) {
                if (!$message instanceof PasswordResetRequestedMessage) {
                    return false;
                }

                return $message->email === 'test@example.com'
                    && strlen($message->resetToken) === 64
                    && $message->resetToken === $user->getPasswordResetToken();
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $result = ($this->handler)($command);

        $this->assertTrue($result);
        $this->assertNotNull($user->getPasswordResetToken());
        $this->assertSame(64, strlen($user->getPasswordResetToken()));
    }

    public function testReturnsFalseWhenUserNotFound(): void
    {
        $email = new Email('notfound@example.com');

        $command = new RequestPasswordResetCommand($email);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn(null);

        $this->userRepository->expects($this->never())
            ->method('save');

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $result = ($this->handler)($command);

        $this->assertFalse($result);
    }
}
