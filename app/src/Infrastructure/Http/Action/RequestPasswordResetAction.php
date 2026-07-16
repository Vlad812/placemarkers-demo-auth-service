<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\RequestPasswordResetCommand;
use App\Application\Handler\RequestPasswordResetHandler;
use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Random\RandomException;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/forgot-password',
    name: 'auth_forgot_password_request',
    methods: ['POST']
)]
#[IsGranted(
    new Expression("!is_granted('IS_AUTHENTICATED_FULLY')"),
    message: 'Already authenticated. Log out before requesting a password reset.',
    statusCode: Response::HTTP_FORBIDDEN,
)]
final class RequestPasswordResetAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly RequestPasswordResetHandler $handler,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ExceptionInterface
     * @throws RandomException
     */
    protected function handleRequest(Request $request): Response
    {
        $requestData = $request->toArray();
        $command = RequestPasswordResetCommand::createFromRawValues($requestData);

        ($this->handler)($command);

        return $this->responder->withStatusCode(Response::HTTP_OK)
            ->respond([
                'message' => 'Если аккаунт с таким email существует, инструкции отправлены на почту.',
            ]);
    }
}
