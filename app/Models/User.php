<?php

namespace App\Models;

use App\Core\Database;

/**
 * User Model
 */
class User
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';

    /**
     * Get all records
     */
    public static function all(): array
    {
        $sql = "SELECT * FROM " . self::$table;
        return Database::fetchAll($sql);
    }

    /**
     * Find record by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE " . self::$primaryKey . " = ?";
        return Database::fetch($sql, [$id]);
    }

    /**
     * Find record by condition
     */
    public static function where(string $column, $value): array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE $column = ?";
        return Database::fetchAll($sql, [$value]);
    }

    /**
     * Find first record by condition
     */
    public static function firstWhere(string $column, $value): ?array
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE $column = ? LIMIT 1";
        return Database::fetch($sql, [$value]);
    }

    /**
     * Create new record
     */
    public static function create(array $data): bool
    {
        return Database::insert(self::$table, $data);
    }

    /**
     * Update record
     */
    public static function update(int $id, array $data): bool
    {
        $where = self::$primaryKey . " = ?";
        return Database::update(self::$table, $data, $where, [$id]);
    }

    /**
     * Delete record
     */
    public static function delete(int $id): bool
    {
        $where = self::$primaryKey . " = ?";
        return Database::delete(self::$table, $where, [$id]);
    }

    /**
     * Get last inserted ID
     */
    public static function lastId(): string
    {
        return Database::lastInsertId();
    }

    /**
     * Count records
     */
    public static function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$table;
        $result = Database::fetch($sql);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Check if record exists
     */
    public static function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$table . " WHERE " . self::$primaryKey . " = ?";
        $result = Database::fetch($sql, [$id]);
        return (int) ($result['total'] ?? 0) > 0;
    }
}
