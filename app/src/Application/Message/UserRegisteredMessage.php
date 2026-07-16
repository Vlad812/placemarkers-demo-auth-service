<?php

declare(strict_types=1);

namespace App\Application\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('email_notifier')]
final readonly class UserRegisteredMessage
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $confirmationToken,
    ) {
    }
}
