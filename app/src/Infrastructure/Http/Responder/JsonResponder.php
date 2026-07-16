<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Responder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class JsonResponder implements ResponderInterface
{
    private SerializerInterface $serializer;

    private int $statusCode = Response::HTTP_OK;

    private array $headers;

    private readonly array $presetHeaders;

    private readonly array $serializationContext;

    public function __construct(
        SerializerInterface $serializer,
        array $presetHeaders = [],
        array $serializationContext = [],
    ) {
        $this->serializer           = $serializer;
        $this->presetHeaders        = $presetHeaders;
        $this->headers              = $presetHeaders;
        $this->serializationContext = $serializationContext;
    }

    public function respond(object|array|null $result = null): JsonResponse
    {
        if (false === (null === $result)) {
            $responseBody = $this->serializer->serialize(
                $result,
                self::SERIALIZATION_FORMAT_JSON,
                $this->serializationContext,
            );
        } else {
            $responseBody = '';
        }

        $jsonResponse = new JsonResponse(
            $responseBody,
            $this->statusCode,
            $this->headers,
            true,
        );

        $this->resetDefaults();

        return $jsonResponse;
    }

    public function fallbackRespondInternalError(): JsonResponse
    {
        $jsonResponse = new JsonResponse(
            null,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $this->presetHeaders,
            false
        );

        $this->resetDefaults();

        return $jsonResponse;
    }

    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    private function resetDefaults(): void
    {
        $this->statusCode = Response::HTTP_OK;
        $this->headers    = $this->presetHeaders;
    }
}
