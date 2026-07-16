<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\UuidGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class SymfonyUuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}

