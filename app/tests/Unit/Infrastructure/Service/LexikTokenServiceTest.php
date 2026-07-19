<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Service;

use App\Domain\Entity\RefreshToken;
use App\Domain\Entity\User;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Service\UuidGeneratorInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\TokenFamily;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Security\SecurityUserAdapter;
use App\Infrastructure\Service\LexikTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;

final class LexikTokenServiceTest extends TestCase
{
    public function testCreateAccessToken(): void
    {
        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            new Email('test@example.com'),
            new PasswordHash(str_repeat('a', 60)),
            Role::user()
        );

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(SecurityUserAdapter::class))
            ->willReturn('jwt-token');

        $refreshTokenRepository = $this->createStub(RefreshTokenRepositoryInterface::class);
        $uuidGenerator = $this->createStub(UuidGeneratorInterface::class);
        $service = new LexikTokenService(
            $jwtManager,
            $refreshTokenRepository,
            $uuidGenerator,
            3600
        );

        $token = $service->createAccessToken($user);

        $this->assertSame('jwt-token', $token);
    }

    public function testCreateRefreshToken(): void
    {
        $user = User::create(
            UserId::fromString('123e4567-e89b-12d3-a456-426614174000'),
            new Email('test@example.com'),
            new PasswordHash(str_repeat('a', 60)),
            Role::user()
        );

        $family = TokenFamily::fromString('family-123');

        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);
        $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);

        $uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn('123e4567-e89b-12d3-a456-426614174001');

        $refreshTokenRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (RefreshToken $refreshToken) use ($user, $family) {
                return $refreshToken->getUserId() === $user->getId()
                    && $refreshToken->getFamily() === $family;
            }));

        $service = new LexikTokenService(
            $jwtManager,
            $refreshTokenRepository,
            $uuidGenerator,
            3600
        );

        $tokenStr = $service->createRefreshToken($user, $family);

        $this->assertIsString($tokenStr);
        $this->assertSame(64, strlen($tokenStr));
    }

    public function testGenerateTokenFamily(): void
    {
        $uuid = '123e4567-e89b-12d3-a456-426614174000';

        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $refreshTokenRepository = $this->createStub(RefreshTokenRepositoryInterface::class);
        $uuidGenerator = $this->createMock(UuidGeneratorInterface::class);

        $uuidGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($uuid);

        $service = new LexikTokenService(
            $jwtManager,
            $refreshTokenRepository,
            $uuidGenerator,
            3600
        );

        $family = $service->generateTokenFamily();

        $this->assertInstanceOf(TokenFamily::class, $family);
        $this->assertSame($uuid, $family->getValue());
    }
}
