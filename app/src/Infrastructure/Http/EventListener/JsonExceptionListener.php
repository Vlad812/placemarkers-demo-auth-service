<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventListener;

use App\Infrastructure\Http\Responder\JsonResponder;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final readonly class JsonExceptionListener
{
    public function __construct(private JsonResponder $responder) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        if (!$e instanceof HttpExceptionInterface) {
            return;
        }

        $response = $this->responder
            ->withStatusCode($e->getStatusCode())
            ->respond(['message' => $e->getMessage()]);

        $response->headers->add($e->getHeaders());

        $event->setResponse($response);
    }
}
