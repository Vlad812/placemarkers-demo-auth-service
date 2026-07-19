<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\TokenFamily;
use PHPUnit\Framework\TestCase;

final class TokenFamilyTest extends TestCase
{
    public function testFromString(): void
    {
        $value = 'some-family-string';
        $family = TokenFamily::fromString($value);
        
        $this->assertSame($value, $family->getValue());
    }

    public function testEqualsReturnsTrueForSameFamily(): void
    {
        $value = 'some-family-string';
        $family1 = TokenFamily::fromString($value);
        $family2 = TokenFamily::fromString($value);
        
        $this->assertTrue($family1->equals($family2));
    }

    public function testEqualsReturnsFalseForDifferentFamily(): void
    {
        $family1 = TokenFamily::fromString('family1');
        $family2 = TokenFamily::fromString('family2');
        
        $this->assertFalse($family1->equals($family2));
    }
}
