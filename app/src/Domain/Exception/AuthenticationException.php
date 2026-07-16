<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class AuthenticationException extends \Exception
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials');
    }

    public static function userInactive(): self
    {
        return new self('Please confirm your email before signing in.');
    }

    public static function tokenExpired(): self
    {
        return new self('Token has expired');
    }

    public static function tokenInvalid(): self
    {
        return new self('Token is invalid');
    }

    public static function tokenReused(): self
    {
        return new self('Token has been reused - possible theft detected');
    }

    public static function userAlreadyExists(): self
    {
        return new self('User with this email already exists');
    }

    public static function emailConfirmationInvalid(): self
    {
        return new self('Email confirmation token is invalid');
    }

    public static function passwordResetInvalid(): self
    {
        return new self('Password reset token is invalid');
    }
}
