<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\ResetPasswordCommand;
use App\Application\Handler\ResetPasswordHandler;
use App\Domain\Exception\AuthenticationException;
use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/reset-password',
    name: 'auth_reset_password',
    methods: ['POST']
)]
final class ResetPasswordAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly ResetPasswordHandler $handler,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @throws AuthenticationException
     */
    protected function handleRequest(Request $request): Response
    {
        $requestData = $request->toArray();
        $command = ResetPasswordCommand::createFromRawValues($requestData);

        ($this->handler)($command);

        return $this->responder->withStatusCode(Response::HTTP_OK)
            ->respond([
                'message' => 'Пароль успешно изменён.',
            ]);
    }
}
