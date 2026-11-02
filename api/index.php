<?php
declare(strict_types=1);
$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === '/') $path = '/index.php';
$pages = ['index.php','login.php','register.php','compose.php','inbox.php','sent.php','starred.php','trash.php','message.php','download.php'];
$actions = ['login','logout','register','language','send_message','toggle_star','mailbox_status','delete_message','bulk_delete','restore_message'];
$allowed = array_map(static fn($page) => '/' . $page, $pages);
foreach ($actions as $action) $allowed[] = '/actions/' . $action . '.php';
if (!in_array($path, $allowed, true)) { http_response_code(404); exit('Not found'); }
$_SERVER['SCRIPT_NAME'] = $path;
if (getenv('VERCEL')) $_SERVER['HTTPS'] = 'on';
try {
    require $root . $path;
} catch (Throwable $error) {
    error_log('Posta request failed: ' . get_class($error));
    http_response_code(503);
    echo 'Posta geçici olarak kullanılamıyor. Lütfen daha sonra tekrar deneyin.';
}
