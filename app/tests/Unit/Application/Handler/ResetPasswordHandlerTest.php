<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\ResetPasswordCommand;
use App\Application\Handler\ResetPasswordHandler;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ResetPasswordHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private ResetPasswordHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new ResetPasswordHandler($this->userRepository, PASSWORD_BCRYPT);
    }

    public function testSuccessfulPasswordReset(): void
    {
        $token = 'valid-reset-token';
        $newPassword = 'newPassword123';

        $command = new ResetPasswordCommand($token, $newPassword);

        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            new Email('test@example.com'),
            new PasswordHash(str_repeat('a', 60)),
            Role::user(),
            true
        );
        $user->issuePasswordResetToken($token);

        $this->userRepository->expects($this->once())
            ->method('findByPasswordResetToken')
            ->with($token)
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        ($this->handler)($command);

        $this->assertNull($user->getPasswordResetToken());
        $this->assertTrue($user->verifyPassword(
            $newPassword,
            fn (string $plain, string $hash) => password_verify($plain, $hash)
        ));
        $this->assertGreaterThanOrEqual(60, strlen($user->getPasswordHash()->getValue()));
    }

    public function testPasswordResetFailsWhenTokenInvalid(): void
    {
        $token = 'invalid-reset-token';

        $command = new ResetPasswordCommand($token, 'password123');

        $this->userRepository->expects($this->once())
            ->method('findByPasswordResetToken')
            ->with($token)
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Password reset token is invalid');

        ($this->handler)($command);
    }
}
