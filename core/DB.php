<?php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class DB
{
    private static array $config = [];
    private static ?PDO $pdo = null;

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        if (empty(self::$config)) {
            throw new RuntimeException('Database configuration is missing.');
        }

        $driver = self::$config['driver'] ?? 'mysql';
        $host = self::$config['host'] ?? '127.0.0.1';
        $port = (int) (self::$config['port'] ?? 3306);
        $database = self::$config['database'] ?? '';
        $charset = self::$config['charset'] ?? 'utf8mb4';
        $username = self::$config['username'] ?? '';
        $password = self::$config['password'] ?? '';
        $collation = self::$config['collation'] ?? 'utf8mb4_unicode_ci';
        $options = self::$config['options'] ?? [];

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $driver,
            $host,
            $port,
            $database,
            $charset
        );

        $defaults = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }

        try {
            self::$pdo = new PDO($dsn, $username, $password, $options);
            self::$pdo->exec('SET NAMES ' . $charset . ' COLLATE ' . $collation);
        } catch (PDOException $exception) {
            throw new RuntimeException('Unable to connect to the database: ' . $exception->getMessage(), 0, $exception);
        }

        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }
}