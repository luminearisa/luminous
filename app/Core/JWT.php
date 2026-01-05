<?php

namespace App\Core;

use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;
use Exception;

/**
 * JWT Class
 * Handle JWT token generation and verification
 */
class JWT
{
    private static ?string $secret = null;
    private static ?string $algo = null;
    private static ?int $expire = null;

    /**
     * Initialize JWT configuration
     */
    private static function init(): void
    {
        if (self::$secret === null) {
            self::$secret = Env::config('jwt.secret');
            self::$algo = Env::config('jwt.algo', 'HS256');
            self::$expire = Env::config('jwt.expire', 3600);

            if (!self::$secret) {
                throw new \RuntimeException('JWT secret not configured');
            }
        }
    }

    /**
     * Generate JWT token
     */
    public static function generate(array $payload, int $expire = null): string
    {
        self::init();

        $issuedAt = time();
        $expiration = $issuedAt + ($expire ?? self::$expire);

        $token = [
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => $payload
        ];

        return FirebaseJWT::encode($token, self::$secret, self::$algo);
    }

    /**
     * Verify and decode JWT token
     */
    public static function verify(string $token): ?array
    {
        self::init();

        try {
            $decoded = FirebaseJWT::decode($token, new Key(self::$secret, self::$algo));
            return (array) $decoded->data;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Decode token without verification
     */
    public static function decode(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            return $payload['data'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if token is expired
     */
    public static function isExpired(string $token): bool
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return true;
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            $exp = $payload['exp'] ?? 0;

            return time() >= $exp;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Get token payload without verification
     */
    public static function getPayload(string $token): ?array
    {
        return self::decode($token);
    }

    /**
     * Refresh token (generate new token with same payload)
     */
    public static function refresh(string $token, int $expire = null): ?string
    {
        $payload = self::verify($token);
        
        if ($payload === null) {
            return null;
        }

        return self::generate($payload, $expire);
    }
}
