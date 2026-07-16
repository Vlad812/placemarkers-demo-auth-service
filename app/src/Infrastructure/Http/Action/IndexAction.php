<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/',
    name: 'Index',
    methods: ['GET']
)]
final class IndexAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function handleRequest(Request $request): Response
    {
        return new Response('Hello World DDD TPL!!!!');
    }
}
