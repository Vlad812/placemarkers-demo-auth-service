<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\LoginCommand;
use App\Application\Handler\LoginHandler;
use App\Domain\Exception\AuthenticationException;
use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/login',
    name: 'auth_login',
    methods: ['POST']
)]
#[IsGranted(
    new Expression("!is_granted('IS_AUTHENTICATED_FULLY')"),
    message: 'Already authenticated. Log out before signing in again.',
    statusCode: Response::HTTP_FORBIDDEN,
)]
final class LoginAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly LoginHandler $handler,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws AuthenticationException
     */
    protected function handleRequest(Request $request): Response
    {
        $requestData = $request->toArray();
        $command = LoginCommand::createFromRawValues($requestData);

        $tokenPair = ($this->handler)($command);

        $response = $this->responder->withStatusCode(Response::HTTP_OK)
            ->respond([
                'access_token' => $tokenPair->accessToken,
                'refresh_token' => $tokenPair->refreshToken,
                'expires_in' => $tokenPair->expiresIn,
                'refresh_expires_in' => $tokenPair->refreshExpiresIn,
                'token_type' => $tokenPair->tokenType,
            ]);

        return $response;
    }
}
