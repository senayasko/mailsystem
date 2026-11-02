<?php
declare(strict_types=1);

// XAMPP varsayılanları; yerel MySQL ayarlarınız farklıysa yalnızca bu dosyayı düzenleyin.
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'mailsystem';
const DB_USER = 'root';
const DB_PASSWORD = '';

function uses_postgres(): bool
{
    return (bool) (getenv('DATABASE_URL_UNPOOLED') ?: getenv('POSTGRES_URL_NON_POOLING') ?: getenv('DATABASE_URL') ?: getenv('POSTGRES_URL'));
}

function create_connection(): PDO
{
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    // Session locking and search_path use a direct connection, not a transaction pool.
    $connectionUrl = getenv('DATABASE_URL_UNPOOLED') ?: getenv('POSTGRES_URL_NON_POOLING') ?: getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
    if ($connectionUrl) {
        $parts = parse_url($connectionUrl);
        if (!$parts || !isset($parts['host'], $parts['user'], $parts['path'])) throw new RuntimeException('Invalid database configuration.');
        $dsn = 'pgsql:host=' . $parts['host'] . ';port=' . ($parts['port'] ?? 5432) . ';dbname=' . ltrim($parts['path'], '/') . ';sslmode=require';
        $pdo = new PDO($dsn, rawurldecode($parts['user']), rawurldecode($parts['pass'] ?? ''), $options);
        $pdo->exec('SET search_path TO posta, public');
        return $pdo;
    }
    return new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASSWORD, $options);
}

function db(): PDO
{
    static $connection = null;
    if ($connection === null) {
        $connection = create_connection();
        if (uses_postgres() && !$connection->query("SELECT to_regclass('posta.sessions')")->fetchColumn()) {
            $connection->beginTransaction();
            try {
                $connection->exec('SELECT pg_advisory_xact_lock(1982041701)');
                $connection->exec(file_get_contents(__DIR__ . '/../migrations/postgres.sql'));
                $connection->commit();
            } catch (Throwable $error) {
                $connection->rollBack();
                throw $error;
            }
        }
    }
    return $connection;
}
