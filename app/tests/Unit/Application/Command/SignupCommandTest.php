<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\SignupCommand;
use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SignupCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = SignupCommand::createFromRawValues([
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(Email::class, $command->email);
        $this->assertSame('user@example.com', $command->email->getValue());
        $this->assertSame('secret123', $command->password);
    }

    #[DataProvider('missingFieldProvider')]
    public function testCreateFromRawValuesMissingFieldThrowsException(array $requestData): void
    {
        $this->expectException(InvalidArgumentException::class);

        SignupCommand::createFromRawValues($requestData);
    }

    public static function missingFieldProvider(): array
    {
        return [
            'missing email' => [['password' => 'secret']],
            'missing password' => [['email' => 'user@example.com']],
        ];
    }
}
