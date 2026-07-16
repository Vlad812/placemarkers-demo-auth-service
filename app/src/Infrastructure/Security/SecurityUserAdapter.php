<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Адаптер Domain User для Symfony Security
 */
final readonly class SecurityUserAdapter implements UserInterface
{
    public function __construct(private User $user)
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->user->getId()->getValue();
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->user->getRole()->getValue())];
    }

    public function getDomainUser(): User
    {
        return $this->user;
    }
}
