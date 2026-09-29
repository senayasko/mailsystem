<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
require_guest();

$first = trim((string) ($_POST['first_name'] ?? ''));
$last = trim((string) ($_POST['last_name'] ?? ''));
$username = mb_strtolower(trim((string) ($_POST['username'] ?? '')), 'UTF-8');
$password = (string) ($_POST['password'] ?? '');
$repeat = (string) ($_POST['password_repeat'] ?? '');
$_SESSION['old_register'] = ['first_name' => $first, 'last_name' => $last, 'username' => $username];

if ($first === '' || $last === '' || mb_strlen($first) > 80 || mb_strlen($last) > 80) {
    flash('error', 'Ad ve soyad 1–80 karakter olmalıdır.');
    redirect('register.php');
}
if (!preg_match('/^[a-z0-9._-]{3,30}$/D', $username)) {
    flash('error', 'Kullanıcı adı 3–30 karakter olmalı; yalnızca küçük harf, rakam, nokta, alt çizgi ve tire içermelidir.');
    redirect('register.php');
}
if (strlen($password) < 8 || strlen($password) > 72) {
    flash('error', 'Şifre 8–72 karakter olmalıdır.');
    redirect('register.php');
}
if ($password !== $repeat) {
    flash('error', 'Şifreler eşleşmiyor.');
    redirect('register.php');
}

try {
    $stmt = db()->prepare('INSERT INTO users (first_name, last_name, username, email, password_hash) VALUES (:first, :last, :username, :email, :hash)');
    $stmt->execute([
        'first' => $first,
        'last' => $last,
        'username' => $username,
        'email' => $username . '@mailsystem.local',
        'hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        flash('error', 'Bu kullanıcı adı zaten kullanılıyor.');
        redirect('register.php');
    }
    throw $exception;
}

unset($_SESSION['old_register']);
flash('success', 'Hesabınız oluşturuldu. Şimdi giriş yapabilirsiniz.');
redirect('login.php');
