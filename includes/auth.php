<?php
declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT id, first_name, last_name, username, email FROM users WHERE id = :id');
        $stmt->execute(['id' => (int) $_SESSION['user_id']]);
        $user = $stmt->fetch() ?: false;
    }
    return $user ?: null;
}

function require_auth(): array
{
    $user = current_user();
    if ($user === null) {
        if (wants_json()) json_response(['error' => t('Oturumun sona erdi. Tekrar giriş yap.')], 401);
        flash('error', t('Devam etmek için giriş yapın.'));
        redirect('login.php');
    }
    return $user;
}

function require_guest(): void
{
    if (current_user() !== null) {
        redirect('inbox.php');
    }
}
