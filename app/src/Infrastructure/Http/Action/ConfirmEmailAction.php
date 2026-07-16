<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\ConfirmEmailCommand;
use App\Application\Handler\ConfirmEmailHandler;
use App\Domain\Exception\AuthenticationException;
use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/confirm-email/{token}',
    name: 'auth_confirm_email',
    methods: ['GET']
)]
final class ConfirmEmailAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly ConfirmEmailHandler $handler,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @throws AuthenticationException|ExceptionInterface
     */
    protected function handleRequest(Request $request): Response
    {
        $requestData = $request->attributes->all();
        $command = ConfirmEmailCommand::createFromRawValues($requestData);

        ($this->handler)($command);

        return $this->responder->withStatusCode(Response::HTTP_OK)
            ->respond([
                'message' => 'Email confirmed successfully',
            ]);
    }
}
