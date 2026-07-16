<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\RefreshTokenCommand;
use App\Application\Handler\RefreshTokenHandler;
use App\Domain\Exception\AuthenticationException;
use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/refresh',
    name: 'auth_refresh',
    methods: ['POST']
)]
final class RefreshTokenAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly RefreshTokenHandler $handler,
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
        $command = RefreshTokenCommand::createFromRawValues($request->toArray());

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
