<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\LogoutCommand;
use App\Application\Handler\LogoutHandler;
use App\Domain\Entity\RefreshToken;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\ValueObject\RefreshTokenId;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\TokenHash;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LogoutHandlerTest extends TestCase
{
    private RefreshTokenRepositoryInterface&MockObject $refreshTokenRepository;
    private LogoutHandler $handler;

    protected function setUp(): void
    {
        $this->refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);
        $this->handler = new LogoutHandler($this->refreshTokenRepository);
    }

    public function testLogoutWithRefreshTokenRevokesFamily(): void
    {
        $userId = '123e4567-e89b-12d3-a456-426614174000';
        $refreshTokenStr = 'some-refresh-token';

        $command = new LogoutCommand($userId, $refreshTokenStr);

        $family = TokenFamily::fromString('family-123');
        $token = RefreshToken::create(
            RefreshTokenId::fromString('aaaaaaaa-bbbb-4ccc-bddd-eeeeeeeeeeee'),
            UserId::fromString($userId),
            new TokenHash($refreshTokenStr),
            $family,
            new DateTimeImmutable('+1 day')
        );

        $this->refreshTokenRepository->expects($this->once())
            ->method('findByToken')
            ->with($this->callback(function (TokenHash $hash) use ($refreshTokenStr) {
                return $hash->getValue() === $refreshTokenStr;
            }))
            ->willReturn($token);

        $this->refreshTokenRepository->expects($this->once())
            ->method('revokeFamily')
            ->with($family);

        $this->refreshTokenRepository->expects($this->never())
            ->method('revokeAllUserTokens');

        ($this->handler)($command);
    }

    public function testLogoutWithRefreshTokenDoesNothingIfTokenNotFound(): void
    {
        $userId = '123e4567-e89b-12d3-a456-426614174000';
        $refreshTokenStr = 'invalid-refresh-token';

        $command = new LogoutCommand($userId, $refreshTokenStr);

        $this->refreshTokenRepository->expects($this->once())
            ->method('findByToken')
            ->willReturn(null);

        $this->refreshTokenRepository->expects($this->never())
            ->method('revokeFamily');

        $this->refreshTokenRepository->expects($this->never())
            ->method('revokeAllUserTokens');

        ($this->handler)($command);
    }

    public function testLogoutWithoutRefreshTokenRevokesAllUserTokens(): void
    {
        $userId = '123e4567-e89b-12d3-a456-426614174000';

        $command = new LogoutCommand($userId, null);

        $this->refreshTokenRepository->expects($this->never())
            ->method('findByToken');

        $this->refreshTokenRepository->expects($this->never())
            ->method('revokeFamily');

        $this->refreshTokenRepository->expects($this->once())
            ->method('revokeAllUserTokens')
            ->with($this->callback(function (UserId $id) use ($userId) {
                return $id->getValue() === $userId;
            }));

        ($this->handler)($command);
    }
}
