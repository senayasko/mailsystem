<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
require_guest();

$identity = mb_strtolower(trim((string) ($_POST['identity'] ?? '')), 'UTF-8');
$password = (string) ($_POST['password'] ?? '');
if ($identity === '' || strlen($identity) > 80 || $password === '') {
    flash('error', 'Kullanıcı adınızı ve şifrenizi girin.');
    redirect('login.php');
}

$stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = :username OR email = :email LIMIT 1');
$stmt->execute(['username' => $identity, 'email' => $identity]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
    flash('error', 'Kullanıcı adı veya şifre hatalı.');
    redirect('login.php');
}

// Oturum sabitleme saldırılarına karşı girişte session kimliğini yenile.
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
redirect('inbox.php');
