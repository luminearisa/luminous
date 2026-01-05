<?php

namespace App\Helpers;

/**
 * String Helper
 * String manipulation utilities
 */
class Str
{
    /**
     * Generate random string
     */
    public static function random(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    /**
     * Convert to slug
     */
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', $separator, $value);
        $value = trim($value, $separator);
        return $value;
    }

    /**
     * Check if string contains substring
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    /**
     * Check if string starts with substring
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Check if string ends with substring
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Limit string length
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit) . $end;
    }
}
