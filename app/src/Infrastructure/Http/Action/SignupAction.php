<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\SignupCommand;
use App\Application\Handler\SignupHandler;
use App\Domain\Exception\AuthenticationException;
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
    '/signup',
    name: 'auth_signup',
    methods: ['POST']
)]
#[IsGranted(
    new Expression("!is_granted('IS_AUTHENTICATED_FULLY')"),
    message: 'Already authenticated. Log out before registering a new account.',
    statusCode: Response::HTTP_FORBIDDEN,
)]
final class SignupAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly SignupHandler $handler,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws AuthenticationException
     * @throws RandomException
     * @throws ExceptionInterface
     */
    protected function handleRequest(Request $request): Response
    {
        $requestData = $request->toArray();
        $command = SignupCommand::createFromRawValues($requestData);

        $user = ($this->handler)($command);

        return $this->responder->withStatusCode(Response::HTTP_CREATED)
                               ->respond([
                                   'id' => $user->getId()->getValue(),
                                   'email' => $user->getEmail()->getValue(),
                                   'message' => 'User registered successfully. Check your email to confirm the account.',
                               ]);
    }
}
