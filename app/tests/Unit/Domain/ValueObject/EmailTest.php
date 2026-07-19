<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('test@example.com');
        $this->assertSame('test@example.com', $email->getValue());
    }

    public function testEmailIsConvertedToLowerCase(): void
    {
        $email = new Email('Test@Example.COM');
        $this->assertSame('test@example.com', $email->getValue());
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        new Email('invalid-email');
    }

    public function testEqualsReturnsTrueForSameEmail(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('Test@Example.com');
        
        $this->assertTrue($email1->equals($email2));
    }

    public function testEqualsReturnsFalseForDifferentEmail(): void
    {
        $email1 = new Email('test1@example.com');
        $email2 = new Email('test2@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }
}
