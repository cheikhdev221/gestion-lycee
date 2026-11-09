<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        self::loadEnvFile(BASE_PATH . '/.env');

        $host = self::env('DB_HOST', self::env('POSTGRES_HOST', 'db'));
        $port = self::env('DB_PORT', self::env('POSTGRES_PORT', '5432'));
        $dbname = self::env('DB_NAME', self::env('POSTGRES_DB', 'gestion_lycee'));
        $username = self::env('DB_USERNAME', self::env('POSTGRES_USER', 'lycee_user'));
        $password = self::env('DB_PASSWORD', self::env('POSTGRES_PASSWORD', 'change-me'));

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);

        try {
            self::$instance = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            throw new \RuntimeException('Connexion PostgreSQL impossible : ' . $exception->getMessage(), 0, $exception);
        }

        return self::$instance;
    }

    private static function loadEnvFile(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value !== false && $value !== null && $value !== '' ? (string) $value : $default;
    }
}
