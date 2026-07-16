<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Exception\AuthenticationException;
use App\Infrastructure\Http\Responder\JsonResponder;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class AbstractAction
{
    protected LoggerInterface $logger;
    protected JsonResponder $responder;

    /**
     * @param LoggerInterface $logger
     * @param JsonResponder $responder
     */
    public function __construct(LoggerInterface $logger, JsonResponder $responder)
    {
        $this->logger = $logger;
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     */
    abstract protected function handleRequest(Request $request): Response;

    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        try {
            return $this->handleRequest($request);
        }
        catch (AuthenticationException $e) {
            $this->logger->warning(
                sprintf('Authentication failed. Message: [%s].', $e->getMessage()),
                ['exception' => $e],
            );

            return $this->responder
                ->withStatusCode(Response::HTTP_UNAUTHORIZED)
                ->respond(['message' => $e->getMessage()]);
        }
        catch (InvalidArgumentException | JsonException $e) {
            $this->logger->error(
                sprintf(
                    'Validation or domain constraint failed. Error: [%s], Message: [%s].',
                    $e::class,
                    $e->getMessage()
                ),
                ['exception' => $e],
            );

            return $this->responder
                ->withStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->respond(['message' => $e->getMessage()]);
        }
        catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Unexpected error. Error: [%s], Message: [%s].',
                    $e::class,
                    $e->getMessage()
                ),
                ['exception' => $e],
            );

           return $this->responder
               ->withStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
               ->respond(['message' => $e->getMessage()]);
        }
    }
}
