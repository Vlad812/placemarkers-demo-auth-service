<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\SignupCommand;
use App\Application\Handler\SignupHandler;
use App\Application\Message\UserRegisteredMessage;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class SignupHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    }

    public function testSuccessfulSignup(): void
    {
        $command = new SignupCommand(new Email('test@example.com'), 'password123');

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($command->email)
            ->willReturn(null);

        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);
        $uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($uuid);

        $this->userRepository->expects($this->once())
            ->method('save');

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use ($uuid) {
                return $message instanceof UserRegisteredMessage
                    && $message->userId === $uuid
                    && $message->email === 'test@example.com'
                    && strlen($message->confirmationToken) === 64;
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $handler = new SignupHandler($this->userRepository, $uuidGenerator, $messageBus);
        $user = ($handler)($command);

        $this->assertSame($uuid, $user->getId()->getValue());
        $this->assertSame('test@example.com', $user->getEmail()->getValue());
        $this->assertFalse($user->isActive());
        $this->assertNotNull($user->getEmailVerificationToken());

        $this->assertTrue($user->verifyPassword('password123', fn ($plain, $hash) => password_verify($plain, $hash)));
    }

    public function testSignupFailsWhenUserExists(): void
    {
        $command = new SignupCommand(new Email('test@example.com'), 'password123');

        $existing = User::create(
            UserId::fromString('aaaaaaaa-bbbb-4ccc-bddd-eeeeeeeeeeee'),
            $command->email,
            new PasswordHash(str_repeat('c', 60)),
            Role::user()
        );

        $uuidGenerator = $this->createStub(UuidGeneratorInterface::class);
        $messageBus = $this->createStub(MessageBusInterface::class);
        $handler = new SignupHandler($this->userRepository, $uuidGenerator, $messageBus);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($command->email)
            ->willReturn($existing);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User with this email already exists');

        ($handler)($command);
    }
}
