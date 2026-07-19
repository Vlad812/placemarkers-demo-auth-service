<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\ResetPasswordCommand;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResetPasswordCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = ResetPasswordCommand::createFromRawValues([
            'token' => 'reset-token',
            'password' => 'new-password',
        ]);

        $this->assertSame('reset-token', $command->token);
        $this->assertSame('new-password', $command->password);
    }

    #[DataProvider('invalidRequestProvider')]
    public function testCreateFromRawValuesThrowsException(array $requestData): void
    {
        $this->expectException(InvalidArgumentException::class);

        ResetPasswordCommand::createFromRawValues($requestData);
    }

    public static function invalidRequestProvider(): array
    {
        return [
            'missing token' => [['password' => 'secret']],
            'missing password' => [['token' => 'reset-token']],
            'empty token' => [['token' => '', 'password' => 'secret']],
            'empty password' => [['token' => 'reset-token', 'password' => '']],
        ];
    }
}
