<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'lexik_jwt_authentication.on_jwt_created')]
final readonly class JWTCreatedListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof SecurityUserAdapter) {
            return;
        }

        $payload = $event->getData();
        $payload['email'] = $user->getDomainUser()->getEmail()->getValue();
        $payload['uuid'] = $user->getDomainUser()->getId()->getValue();

        $event->setData($payload);
    }
}
