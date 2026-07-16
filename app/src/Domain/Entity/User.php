<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\Role;
use App\Domain\ValueObject\UserId;
use DateTimeImmutable;

final class User
{
    private UserId $id;
    private Email $email;
    private PasswordHash $passwordHash;
    private Role $role;
    private bool $isActive;
    private ?string $emailVerificationToken;
    private ?string $passwordResetToken;
    private ?DateTimeImmutable $emailVerifiedAt;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        UserId             $id,
        Email              $email,
        PasswordHash       $passwordHash,
        Role               $role,
        bool               $isActive = false,
        ?string            $emailVerificationToken = null,
        ?string            $passwordResetToken = null,
        ?DateTimeImmutable $emailVerifiedAt = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->isActive = $isActive;
        $this->emailVerificationToken = $emailVerificationToken;
        $this->passwordResetToken = $passwordResetToken;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public static function create(
        UserId $id,
        Email $email,
        PasswordHash $passwordHash,
        Role $role,
        bool $isActive = false
    ): self {
        return new self($id, $email, $passwordHash, $role, $isActive);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function verifyPassword(string $plainPassword, callable $verifyCallback): bool
    {
        return ($verifyCallback)($plainPassword, $this->passwordHash->getValue());
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function issueEmailVerificationToken(string $token): void
    {
        $this->emailVerificationToken = $token;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function confirmEmail(): void
    {
        $this->isActive = true;
        $this->emailVerificationToken = null;
        $this->emailVerifiedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function issuePasswordResetToken(string $token): void
    {
        $this->passwordResetToken = $token;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function clearPasswordResetToken(): void
    {
        $this->passwordResetToken = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePassword(PasswordHash $newPasswordHash): void
    {
        $this->passwordHash = $newPasswordHash;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'email' => $this->email->getValue(),
            'role' => $this->role->getValue(),
            'isActive' => $this->isActive,
            'emailVerifiedAt' => $this->emailVerifiedAt?->format(\DateTime::ATOM),
            'createdAt' => $this->createdAt->format(\DateTime::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTime::ATOM),
        ];
    }
}
