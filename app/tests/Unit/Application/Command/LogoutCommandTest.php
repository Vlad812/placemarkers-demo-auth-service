<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\LogoutCommand;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class LogoutCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = LogoutCommand::createFromRawValues(
            ['refresh_token' => 'refresh-token'],
            'user-id-123',
        );

        $this->assertSame('user-id-123', $command->userId);
        $this->assertSame('refresh-token', $command->refreshToken);
    }

    public function testCreateFromRawValuesWithoutRefreshToken(): void
    {
        $command = LogoutCommand::createFromRawValues([], 'user-id-123');

        $this->assertSame('user-id-123', $command->userId);
        $this->assertNull($command->refreshToken);
    }

    public function testCreateFromRawValuesEmptyUserIdThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User id is required for logout.');

        LogoutCommand::createFromRawValues([], '');
    }
}
