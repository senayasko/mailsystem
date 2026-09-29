<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Uygulama htdocs/mailsystem altında veya farklı bir klasör adında çalışabilir.
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/mailsystem/index.php');
$directory = str_replace('\\', '/', dirname($script));
if (basename($directory) === 'actions') {
    $directory = dirname($directory);
}
define('BASE_URL', rtrim($directory, '/') === '' ? '' : rtrim($directory, '/'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('mailsystem_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => BASE_URL === '' ? '/' : BASE_URL . '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
