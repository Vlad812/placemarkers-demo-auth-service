<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\LoginCommand;
use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class LoginCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = LoginCommand::createFromRawValues([
            'email' => 'user@example.com',
            'password' => 'secret123',
        ]);

        $this->assertInstanceOf(Email::class, $command->email);
        $this->assertSame('user@example.com', $command->email->getValue());
        $this->assertSame('secret123', $command->password);
    }

    public function testCreateFromRawValuesMissingEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LoginCommand::createFromRawValues(['password' => 'secret']);
    }
}
