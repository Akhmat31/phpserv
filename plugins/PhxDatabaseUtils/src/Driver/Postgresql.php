<?php

namespace PhxPlugins\Databaseutils\Driver;

use PDO;
use PDOException;

class Postgresql
{
    public static function connect(array $config): PDO
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5432;
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? 'postgres';
        $password = $config['password'] ?? '';
        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException("PostgreSQL connection failed: " . $e->getMessage());
        }
    }
}
