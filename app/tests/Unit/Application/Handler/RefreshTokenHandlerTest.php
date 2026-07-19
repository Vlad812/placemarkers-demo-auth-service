<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\RefreshTokenCommand;
use App\Application\DTO\TokenPairDTO;
use App\Application\Handler\RefreshTokenHandler;
use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Exception\AuthenticationException;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\TokenServiceInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\RefreshTokenId;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RefreshTokenHandlerTest extends TestCase
{
    private RefreshTokenRepositoryInterface&MockObject $refreshTokenRepository;

    protected function setUp(): void
    {
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);
    }

    public function testSuccessfulTokenRefresh(): void
    {
        $refreshTokenStr = 'valid-refresh-token';
        $command = new RefreshTokenCommand($refreshTokenStr);

        $userId = UserId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $family = TokenFamily::fromString('family-123');

        $refreshToken = RefreshToken::create(
            RefreshTokenId::fromString('aaaaaaaa-bbbb-4ccc-bddd-eeeeeeeeeeee'),
            $userId,
            new TokenHash($refreshTokenStr),
            $family,
            new DateTimeImmutable('+1 day')
        );

        $this->refreshTokenRepository->expects($this->once())
            ->method('findByToken')
            ->with($this->callback(fn (TokenHash $hash) => $hash->getValue() === $refreshTokenStr))
            ->willReturn($refreshToken);

        $this->refreshTokenRepository->expects($this->once())
            ->method('save')
            ->with($refreshToken);

        $passwordHash = PasswordHash::fromPlainPassword(
            'secret123456',
            fn (string $plain) => password_hash($plain, PASSWORD_BCRYPT)
        );
        $user = User::create(
            $userId,
            new Email('test@example.com'),
            $passwordHash,
            Role::user(),
            true
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $tokenService = $this->createMock(TokenServiceInterface::class);
        $newAccessToken = 'new-access-token';
        $newRefreshToken = 'new-refresh-token';

        $tokenService->expects($this->once())
            ->method('createAccessToken')
            ->with($user)
            ->willReturn($newAccessToken);

        $tokenService->expects($this->once())
            ->method('createRefreshToken')
            ->with($user, $family)
            ->willReturn($newRefreshToken);

        $handler = new RefreshTokenHandler(
            $this->refreshTokenRepository,
            $userRepository,
            $tokenService,
            3600,
            2592000
        );

        $result = ($handler)($command);

        $this->assertInstanceOf(TokenPairDTO::class, $result);
        $this->assertSame($newAccessToken, $result->accessToken);
        $this->assertSame($newRefreshToken, $result->refreshToken);
        $this->assertNotNull($refreshToken->getUsedAt());
    }

    public function testRefreshFailsWhenTokenNotFound(): void
    {
        $command = new RefreshTokenCommand('invalid-token');

        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $tokenService = $this->createStub(TokenServiceInterface::class);
        $handler = new RefreshTokenHandler(
            $this->refreshTokenRepository,
            $userRepository,
            $tokenService,
            3600,
            2592000
        );

        $this->refreshTokenRepository->expects($this->once())
            ->method('findByToken')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token is invalid');

        ($handler)($command);
    }

    public function testRefreshFailsAndRevokesFamilyWhenTokenReused(): void
    {
        $command = new RefreshTokenCommand('reused-token');

        $family = TokenFamily::fromString('family-123');

        $refreshToken = new RefreshToken(
            RefreshTokenId::fromString('bbbbbbbb-bbbb-4ccc-bddd-eeeeeeeeeeee'),
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            new TokenHash('reused-token'),
            $family,
            new DateTimeImmutable('+1 day'),
            null,
            new DateTimeImmutable()
        );

        $this->assertFalse($refreshToken->isValid());

        $userRepository = $this->createStub(UserRepositoryInterface::class);
        $tokenService = $this->createStub(TokenServiceInterface::class);
        $handler = new RefreshTokenHandler(
            $this->refreshTokenRepository,
            $userRepository,
            $tokenService,
            3600,
            2592000
        );

        $this->refreshTokenRepository->expects($this->once())
            ->method('findByToken')
            ->willReturn($refreshToken);

        $this->refreshTokenRepository->expects($this->once())
            ->method('revokeFamily')
            ->with($family);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token has been reused - possible theft detected');

        ($handler)($command);
    }

    public function testRefreshFailsWhenUserNotFoundOrInactive(): void
    {
        $command = new RefreshTokenCommand('valid-token');

        $userId = UserId::fromString('123e4567-e89b-12d3-a456-426614174000');

        $refreshToken = RefreshToken::create(
            RefreshTokenId::fromString('cccccccc-bbbb-4ccc-bddd-eeeeeeeeeeee'),
            $userId,
            new TokenHash('valid-token'),
            TokenFamily::fromString('family-xyz'),
            new DateTimeImmutable('+1 day')
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $tokenService = $this->createStub(TokenServiceInterface::class);
        $handler = new RefreshTokenHandler(
            $this->refreshTokenRepository,
            $userRepository,
            $tokenService,
            3600,
            2592000
        );

        $this->refreshTokenRepository->expects($this->once())
            ->method('findByToken')
            ->willReturn($refreshToken);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Token is invalid');

        ($handler)($command);
    }
}
