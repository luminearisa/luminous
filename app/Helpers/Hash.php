<?php

namespace App\Helpers;

/**
 * Hash Helper
 * Password hashing utilities
 */
class Hash
{
    /**
     * Hash password
     */
    public static function make(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if hash needs rehash
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT);
    }
}
