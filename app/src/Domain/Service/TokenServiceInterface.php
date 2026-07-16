<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\ValueObject\TokenFamily;

interface TokenServiceInterface
{
    /**
     * Создает JWT access token
     */
    public function createAccessToken(User $user): string;

    /**
     * Создает refresh token и сохраняет его
     */
    public function createRefreshToken(User $user, TokenFamily $family): string;

    /**
     * Проверяет и декодирует access token
     */
    public function decodeAccessToken(string $token): array;

    /**
     * Получает семейство токенов для новой сессии
     */
    public function generateTokenFamily(): TokenFamily;
}
