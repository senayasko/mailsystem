<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$user = require_auth();

$recipientInput = trim((string) ($_POST['recipient'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$body = trim((string) ($_POST['body'] ?? ''));
$_SESSION['old_compose'] = ['recipient' => $recipientInput, 'subject' => $subject, 'body' => $body];
$username = recipient_username($recipientInput);
if ($username === null) {
    flash('error', 'Alıcı için geçerli bir kullanıcı adı veya @mailsystem.local adresi girin.');
    redirect('compose.php');
}
if ($subject === '' || mb_strlen($subject, 'UTF-8') > 180) {
    flash('error', 'Konu 1–180 karakter olmalıdır.');
    redirect('compose.php');
}
if ($body === '' || mb_strlen($body, 'UTF-8') > 20000) {
    flash('error', 'Mesaj 1–20.000 karakter olmalıdır.');
    redirect('compose.php');
}

$stmt = db()->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
$stmt->execute(['username' => $username]);
$recipient = $stmt->fetch();
if (!$recipient) {
    flash('error', 'Alıcı bulunamadı. Sisteme kayıtlı bir kullanıcı adı girin.');
    redirect('compose.php');
}

$stmt = db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (:sender, :recipient, :subject, :body)');
$stmt->execute([
    'sender' => $user['id'],
    'recipient' => $recipient['id'],
    'subject' => $subject,
    'body' => $body,
]);
unset($_SESSION['old_compose']);
flash('success', 'Mesaj gönderildi. Gönderilenler bölümünde görebilirsiniz.');
redirect('sent.php');
