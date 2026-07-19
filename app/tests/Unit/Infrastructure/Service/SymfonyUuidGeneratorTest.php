<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\SymfonyUuidGenerator;
use PHPUnit\Framework\TestCase;

final class SymfonyUuidGeneratorTest extends TestCase
{
    public function testGenerateReturnsValidUuidV7(): void
    {
        $generator = new SymfonyUuidGenerator();
        $uuid = $generator->generate();

        $this->assertIsString($uuid);
        $this->assertSame(36, strlen($uuid));

        // Basic UUID v7 format check
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }
}
