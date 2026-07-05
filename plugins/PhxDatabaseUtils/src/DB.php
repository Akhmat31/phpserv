<?php

namespace PhxPlugins\Databaseutils;

use PDO;
use PDOException;
use PhxPlugins\Databaseutils\Driver\Mysql;
use PhxPlugins\Databaseutils\Driver\Postgresql;

class DB
{
    private static ?PDO $connection = null;
    private static array $config = [];
    private static string $driver = 'mysql';

    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$driver = $config['driver'] ?? 'mysql';
    }
    public static function connection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }
    private static function connect(): void
    {
        try {
            if (self::$driver === 'mysql') {
                self::$connection = Mysql::connect(self::$config);
            } elseif (self::$driver === 'postgresql') {
                self::$connection = Postgresql::connect(self::$config);
            } else {
                throw new PDOException("Unsupported database driver: " . self::$driver);
            }
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }
    public static function select(string $query, array $params = []): array
    {
        $stmt = self::connection()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = self::connection()->prepare($query);
        $stmt->execute(array_values($data));
        
        return (int) self::connection()->lastInsertId();
    }
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($key) => "{$key} = ?", array_keys($data)));
        $query = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $stmt = self::connection()->prepare($query);
        $stmt->execute(array_merge(array_values($data), $whereParams));
        
        return $stmt->rowCount();
    }
    public static function delete(string $table, string $where, array $params = []): int
    {
        $query = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::connection()->prepare($query);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    public static function query(string $query, array $params = []): bool
    {
        $stmt = self::connection()->prepare($query);
        return $stmt->execute($params);
    }
    public static function beginTransaction(): bool
    {
        return self::connection()->beginTransaction();
    }
    public static function commit(): bool
    {
        return self::connection()->commit();
    }
    public static function rollback(): bool
    {
        return self::connection()->rollBack();
    }
    public static function lastInsertId(): string
    {
        return self::connection()->lastInsertId();
    }
    public static function disconnect(): void
    {
        self::$connection = null;
    }
}