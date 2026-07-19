<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\RefreshTokenCommand;
use App\Domain\Exception\AuthenticationException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RefreshTokenCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = RefreshTokenCommand::createFromRawValues([
            'refresh_token' => 'token-value',
        ]);

        $this->assertSame('token-value', $command->refreshToken);
    }

    public function testCreateFromRawValuesMissingTokenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RefreshTokenCommand::createFromRawValues([]);
    }

    #[DataProvider('emptyTokenProvider')]
    public function testCreateFromRawValuesEmptyTokenThrowsAuthenticationException(mixed $token): void
    {
        $this->expectException(AuthenticationException::class);

        RefreshTokenCommand::createFromRawValues(['refresh_token' => $token]);
    }

    public static function emptyTokenProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
        ];
    }
}
