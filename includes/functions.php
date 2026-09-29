<?php
declare(strict_types=1);

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url($path), true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $given = $_POST['csrf_token'] ?? '';
    if (!is_string($given) || !hash_equals(csrf_token(), $given)) {
        http_response_code(403);
        exit('İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.');
    }
}

function only_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Bu işlem için POST isteği gereklidir.');
    }
}

function initial(string $name): string
{
    return mb_strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'), 'UTF-8');
}

function display_date(string $value): string
{
    return date('d.m.Y H:i', strtotime($value));
}

function preview(string $body): string
{
    $plain = trim(preg_replace('/\s+/u', ' ', $body) ?? $body);
    return mb_strlen($plain, 'UTF-8') > 110 ? mb_substr($plain, 0, 110, 'UTF-8') . '…' : $plain;
}

function recipient_username(string $value): ?string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    if (str_contains($value, '@')) {
        $parts = explode('@', $value);
        if (count($parts) !== 2 || $parts[1] !== 'mailsystem.local') {
            return null;
        }
        $value = $parts[0];
    }
    return preg_match('/^[a-z0-9._-]{3,30}$/D', $value) ? $value : null;
}
