<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Command\LogoutCommand;
use App\Application\Handler\LogoutHandler;
use App\Infrastructure\Http\AbstractAction;
use App\Infrastructure\Http\Responder\JsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/logout',
    name: 'auth_logout',
    methods: ['POST']
)]
#[IsGranted('ROLE_USER')]
final class LogoutAction extends AbstractAction
{
    public function __construct(
        LoggerInterface $logger,
        JsonResponder $responder,
        private readonly LogoutHandler $handler,
        private readonly Security $security,
    ) {
        parent::__construct($logger, $responder);
    }

    /**
     * @param Request $request
     * @return Response
     */
    protected function handleRequest(Request $request): Response
    {
        $user = $this->security->getUser();
        if ($user === null) {
            return $this->responder->withStatusCode(Response::HTTP_UNAUTHORIZED)
                ->respond(['message' => 'Authentication required.']);
        }

        $command = LogoutCommand::createFromRawValues($request->toArray(), $user->getUserIdentifier());

        ($this->handler)($command);

        $response = $this->responder->withStatusCode(Response::HTTP_OK)
            ->respond(['message' => 'Logged out successfully']);

        return $response;
    }
}
