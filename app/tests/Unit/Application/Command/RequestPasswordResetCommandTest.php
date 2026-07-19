<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\RequestPasswordResetCommand;
use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RequestPasswordResetCommandTest extends TestCase
{
    public function testCreateFromRawValuesSuccess(): void
    {
        $command = RequestPasswordResetCommand::createFromRawValues([
            'email' => 'user@example.com',
        ]);

        $this->assertInstanceOf(Email::class, $command->email);
        $this->assertSame('user@example.com', $command->email->getValue());
    }

    public function testCreateFromRawValuesMissingEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RequestPasswordResetCommand::createFromRawValues([]);
    }
}
