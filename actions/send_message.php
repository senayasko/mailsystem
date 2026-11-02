<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/attachments.php';
only_post();
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 4 * 1024 * 1024) {
    require_auth();
    flash('error', t('Dosya en fazla 2 MB olabilir. Daha küçük bir dosya seçin.'));
    redirect('compose.php');
}
verify_csrf();
$user = require_auth();

$recipientInput = trim((string) ($_POST['recipient'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$body = trim((string) ($_POST['body'] ?? ''));
$_SESSION['old_compose'] = ['recipient' => $recipientInput, 'subject' => $subject, 'body' => $body];
$username = recipient_username($recipientInput);
if ($username === null) {
    flash('error', t('Alıcı için geçerli bir kullanıcı adı veya @mailsystem.local adresi girin.'));
    redirect('compose.php');
}
if ($subject === '' || mb_strlen($subject, 'UTF-8') > 180) {
    flash('error', t('Konu 1–180 karakter olmalıdır.'));
    redirect('compose.php');
}
if ($body === '' || mb_strlen($body, 'UTF-8') > 20000) {
    flash('error', t('Mesaj 1–20.000 karakter olmalıdır.'));
    redirect('compose.php');
}

$stmt = db()->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
$stmt->execute(['username' => $username]);
$recipient = $stmt->fetch();
if (!$recipient) {
    flash('error', t('Alıcı bulunamadı. Sisteme kayıtlı bir kullanıcı adı girin.'));
    redirect('compose.php');
}

$savedPath = null;
try {
    if (isset($_FILES['attachment']) && !is_array($_FILES['attachment'])) {
        throw new RuntimeException(t('Dosya yükleme isteği geçersiz.'));
    }
    $attachment = validate_attachment($_FILES['attachment'] ?? null);
} catch (RuntimeException $error) {
    flash('error', $error->getMessage() . ' ' . t('Dosyayı yeniden seçmen gerekecek.'));
    redirect('compose.php');
}

try {
    // Dosya veya veritabanı işlemi başarısızsa yarım mesaj bırakma.
    db()->beginTransaction();
    $stmt = db()->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body) VALUES (:sender, :recipient, :subject, :body)' . (uses_postgres() ? ' RETURNING id' : ''));
    $stmt->execute(['sender' => $user['id'], 'recipient' => $recipient['id'], 'subject' => $subject, 'body' => $body]);
    $messageId = (int) (uses_postgres() ? $stmt->fetchColumn() : db()->lastInsertId());
    if ($attachment && uses_postgres()) {
        $bytes = file_get_contents($attachment['tmp_name']);
        if ($bytes === false) throw new RuntimeException('Dosya okunamadı.');
        $stmt = db()->prepare('INSERT INTO attachments (message_id,original_name,stored_name,mime_type,size_bytes,content_base64) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$messageId, $attachment['original_name'], $attachment['stored_name'], $attachment['mime_type'], $attachment['size_bytes'], base64_encode($bytes)]);
    } elseif ($attachment) {
        $directory = attachment_directory();
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Dosya klasörü oluşturulamadı.');
        }
        $savedPath = $directory . '/' . $attachment['stored_name'];
        if (!move_uploaded_file($attachment['tmp_name'], $savedPath)) {
            throw new RuntimeException('Dosya kaydedilemedi.');
        }
        $stmt = db()->prepare('INSERT INTO attachments (message_id, original_name, stored_name, mime_type, size_bytes) VALUES (:message, :original, :stored, :mime, :size)');
        $stmt->execute(['message' => $messageId, 'original' => $attachment['original_name'], 'stored' => $attachment['stored_name'], 'mime' => $attachment['mime_type'], 'size' => $attachment['size_bytes']]);
    }
    db()->commit();
} catch (Throwable $error) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    if ($savedPath && is_file($savedPath)) {
        unlink($savedPath);
    }
    error_log('Mesaj/ek kaydetme hatası: ' . $error->getMessage());
    flash('error', t('Mesaj kaydedilemedi. Lütfen tekrar dene; ek varsa dosyayı yeniden seç.'));
    redirect('compose.php');
}
unset($_SESSION['old_compose']);
flash('success', t('Mesaj gönderildi. Gönderilenler bölümünde görebilirsiniz.'));
redirect('sent.php');
