<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/attachments.php';
$user = require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Her indirmede mesajın göndereni veya alıcısı olma şartını doğrula.
$stmt = db()->prepare('SELECT a.* FROM attachments a JOIN messages m ON m.id = a.message_id WHERE a.id = :id AND (m.sender_id = :sender OR m.recipient_id = :recipient)');
$stmt->execute(['id' => $id ?: 0, 'sender' => $user['id'], 'recipient' => $user['id']]);
$attachment = $stmt->fetch();
if (!$attachment || !preg_match('/^[a-f0-9]{48}\.bin$/D', $attachment['stored_name'])) {
    http_response_code(404);
    exit(t('Dosya bulunamadı veya indirme yetkin yok.'));
}
$path = attachment_directory() . '/' . $attachment['stored_name'];
if (uses_postgres()) {
    $bytes = base64_decode($attachment['content_base64'], true);
    $handle = $bytes === false ? false : fopen('php://temp', 'w+b');
    if ($handle) { fwrite($handle, $bytes); rewind($handle); }
} else {
    $handle = @fopen($path, 'rb');
}
if (!$handle) {
    http_response_code(404);
    exit(t('Dosya bulunamadı.'));
}
session_write_close();
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Disposition: attachment; filename="attachment"; filename*=UTF-8\'\'' . rawurlencode($attachment['original_name']));
header('Content-Length: ' . (string) $attachment['size_bytes']);
fpassthru($handle);
fclose($handle);
exit;
