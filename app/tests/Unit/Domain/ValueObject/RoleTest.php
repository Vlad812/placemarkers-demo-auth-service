<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Role;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testValidRole(): void
    {
        $roleAdmin = new Role(Role::ADMIN);
        $this->assertSame(Role::ADMIN, $roleAdmin->getValue());
        
        $roleUser = new Role(Role::USER);
        $this->assertSame(Role::USER, $roleUser->getValue());
    }

    public function testInvalidRoleThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role');
        
        new Role('guest');
    }

    public function testAdminFactory(): void
    {
        $role = Role::admin();
        $this->assertSame(Role::ADMIN, $role->getValue());
        $this->assertTrue($role->isAdmin());
    }

    public function testUserFactory(): void
    {
        $role = Role::user();
        $this->assertSame(Role::USER, $role->getValue());
        $this->assertFalse($role->isAdmin());
    }
}
