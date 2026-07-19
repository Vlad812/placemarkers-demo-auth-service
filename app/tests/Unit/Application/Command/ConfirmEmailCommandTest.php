<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\ConfirmEmailCommand;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfirmEmailCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = ConfirmEmailCommand::createFromRawValues([
            'token' => 'confirm-token',
        ]);

        $this->assertSame('confirm-token', $command->token);
    }

    #[DataProvider('invalidRequestProvider')]
    public function testCreateFromRawValuesThrowsException(array $requestData): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConfirmEmailCommand::createFromRawValues($requestData);
    }

    public static function invalidRequestProvider(): array
    {
        return [
            'missing token' => [[]],
            'empty token' => [['token' => '']],
        ];
    }
}
