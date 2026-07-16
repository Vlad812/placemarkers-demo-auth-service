<?php
// Infrastructure/Persistence/Doctrine/Entity/UserRole.php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
