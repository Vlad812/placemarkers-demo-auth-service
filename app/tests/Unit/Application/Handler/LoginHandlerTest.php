<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\LoginCommand;
use App\Application\DTO\TokenPairDTO;
use App\Application\Handler\LoginHandler;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\TokenServiceInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\UserId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LoginHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
    }

    public function testSuccessfulLogin(): void
    {
        $command = new LoginCommand(new Email('test@example.com'), 'password123');

        $passwordHash = PasswordHash::fromPlainPassword(
            'password123',
            fn (string $plain) => password_hash($plain, PASSWORD_BCRYPT)
        );
        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            $command->email,
            $passwordHash,
            Role::user(),
            true
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($command->email)
            ->willReturn($user);

        $tokenService = $this->createMock(TokenServiceInterface::class);
        $family = TokenFamily::fromString('family-123');
        $tokenService->expects($this->once())
            ->method('generateTokenFamily')
            ->willReturn($family);

        $accessToken = 'access.token.string';
        $refreshToken = 'refresh.token.string';

        $tokenService->expects($this->once())
            ->method('createAccessToken')
            ->with($user)
            ->willReturn($accessToken);

        $tokenService->expects($this->once())
            ->method('createRefreshToken')
            ->with($user, $family)
            ->willReturn($refreshToken);

        $handler = new LoginHandler($this->userRepository, $tokenService, 3600, 2592000);
        $result = ($handler)($command);

        $this->assertInstanceOf(TokenPairDTO::class, $result);
        $this->assertSame($accessToken, $result->accessToken);
        $this->assertSame($refreshToken, $result->refreshToken);
        $this->assertSame(3600, $result->expiresIn);
        $this->assertSame(2592000, $result->refreshExpiresIn);
    }

    public function testLoginFailsIfUserNotFound(): void
    {
        $command = new LoginCommand(new Email('test@example.com'), 'password123');

        $tokenService = $this->createStub(TokenServiceInterface::class);
        $handler = new LoginHandler($this->userRepository, $tokenService, 3600, 2592000);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($command->email)
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        ($handler)($command);
    }

    public function testLoginFailsIfUserInactive(): void
    {
        $command = new LoginCommand(new Email('test@example.com'), 'password123');

        $passwordHash = PasswordHash::fromPlainPassword(
            'password123',
            fn (string $plain) => password_hash($plain, PASSWORD_BCRYPT)
        );
        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            $command->email,
            $passwordHash,
            Role::user(),
            false
        );

        $tokenService = $this->createStub(TokenServiceInterface::class);
        $handler = new LoginHandler($this->userRepository, $tokenService, 3600, 2592000);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($command->email)
            ->willReturn($user);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Please confirm your email before signing in.');

        ($handler)($command);
    }

    public function testLoginFailsIfPasswordIncorrect(): void
    {
        $command = new LoginCommand(new Email('test@example.com'), 'wrongpassword');

        $passwordHash = PasswordHash::fromPlainPassword(
            'password123',
            fn (string $plain) => password_hash($plain, PASSWORD_BCRYPT)
        );
        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            $command->email,
            $passwordHash,
            Role::user(),
            true
        );

        $tokenService = $this->createStub(TokenServiceInterface::class);
        $handler = new LoginHandler($this->userRepository, $tokenService, 3600, 2592000);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($command->email)
            ->willReturn($user);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        ($handler)($command);
    }
}
