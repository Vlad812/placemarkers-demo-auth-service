<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\TokenHash;
use PHPUnit\Framework\TestCase;

final class TokenHashTest extends TestCase
{
    public function testConstruct(): void
    {
        $value = 'some-hash-value';
        $hash = new TokenHash($value);
        
        $this->assertSame($value, $hash->getValue());
    }

    public function testGenerateCreatesValidTokenHash(): void
    {
        $hash = TokenHash::generate();
        
        // bin2hex(random_bytes(32)) is 64 characters long
        $this->assertSame(64, strlen($hash->getValue()));
        
        // Ensure consecutive generations are unique
        $hash2 = TokenHash::generate();
        $this->assertNotSame($hash->getValue(), $hash2->getValue());
    }

    public function testEqualsReturnsTrueForSameString(): void
    {
        $value = 'some-hash-value';
        $hash = new TokenHash($value);
        
        $this->assertTrue($hash->equals($value));
    }

    public function testEqualsReturnsFalseForDifferentString(): void
    {
        $hash = new TokenHash('some-hash-value');
        
        $this->assertFalse($hash->equals('different-hash-value'));
    }
}
