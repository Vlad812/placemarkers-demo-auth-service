<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder;

use Symfony\Component\HttpFoundation\Response;

interface ResponderInterface
{
    public const string SERIALIZATION_FORMAT_JSON = 'json';

    public function respond(object|array|null $result = null): Response;

    public function fallbackRespondInternalError(): Response;

    public function withStatusCode(int $statusCode): self;

    public function withHeaders(array $headers): self;
}
