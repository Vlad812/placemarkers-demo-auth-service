<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private UserId $userId;
    private Email $email;
    private PasswordHash $passwordHash;
    private Role $role;

    protected function setUp(): void
    {
        $this->userId = UserId::fromString('123e4567-e89b-12d3-a456-426614174000');
        $this->email = new Email('test@example.com');
        $this->passwordHash = new PasswordHash(str_repeat('a', 60));
        $this->role = Role::user();
    }

    public function testCreateUser(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);

        $this->assertSame($this->userId, $user->getId());
        $this->assertSame($this->email, $user->getEmail());
        $this->assertSame($this->passwordHash, $user->getPasswordHash());
        $this->assertSame($this->role, $user->getRole());
        $this->assertFalse($user->isActive());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertNull($user->getPasswordResetToken());
        $this->assertNull($user->getEmailVerifiedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testVerifyPassword(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);
        
        $verifyCallback = function (string $plain, string $hash) {
            return $plain === 'password' && $hash === str_repeat('a', 60);
        };

        $this->assertTrue($user->verifyPassword('password', $verifyCallback));
        $this->assertFalse($user->verifyPassword('wrong', $verifyCallback));
    }

    public function testDeactivate(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role, true);
        $this->assertTrue($user->isActive());

        $updatedAt = $user->getUpdatedAt();
        
        // Simulating a slight delay to ensure time difference
        sleep(1); 
        $user->deactivate();

        $this->assertFalse($user->isActive());
        $this->assertGreaterThan($updatedAt, $user->getUpdatedAt());
    }

    public function testIssueEmailVerificationToken(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);
        $token = 'verify-me';
        
        $user->issueEmailVerificationToken($token);

        $this->assertSame($token, $user->getEmailVerificationToken());
    }

    public function testConfirmEmail(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);
        $user->issueEmailVerificationToken('token');
        
        $user->confirmEmail();

        $this->assertTrue($user->isActive());
        $this->assertNull($user->getEmailVerificationToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getEmailVerifiedAt());
    }

    public function testIssueAndClearPasswordResetToken(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);
        
        $user->issuePasswordResetToken('reset-token');
        $this->assertSame('reset-token', $user->getPasswordResetToken());

        $user->clearPasswordResetToken();
        $this->assertNull($user->getPasswordResetToken());
    }

    public function testUpdatePassword(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);
        
        $newPasswordHash = new PasswordHash(str_repeat('b', 60));
        $user->updatePassword($newPasswordHash);

        $this->assertSame($newPasswordHash, $user->getPasswordHash());
    }

    public function testToArray(): void
    {
        $user = User::create($this->userId, $this->email, $this->passwordHash, $this->role);
        
        $array = $user->toArray();
        
        $this->assertIsArray($array);
        $this->assertSame($this->userId->getValue(), $array['id']);
        $this->assertSame($this->email->getValue(), $array['email']);
        $this->assertSame($this->role->getValue(), $array['role']);
        $this->assertFalse($array['isActive']);
        $this->assertNull($array['emailVerifiedAt']);
        $this->assertArrayHasKey('createdAt', $array);
        $this->assertArrayHasKey('updatedAt', $array);
    }
}
