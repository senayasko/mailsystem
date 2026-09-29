<?php
declare(strict_types=1);

// XAMPP varsayılanları; yerel MySQL ayarlarınız farklıysa yalnızca bu dosyayı düzenleyin.
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'mailsystem';
const DB_USER = 'root';
const DB_PASSWORD = '';

function db(): PDO
{
    static $connection = null;
    if ($connection === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $connection = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $connection;
}
