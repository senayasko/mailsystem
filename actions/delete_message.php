<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$user = require_auth();
$id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('inbox.php');
}
$stmt = db()->prepare('SELECT sender_id, recipient_id FROM messages WHERE id=:id AND (sender_id=:viewer_sender OR recipient_id=:viewer_recipient)');
$stmt->execute(['id' => $id, 'viewer_sender' => $user['id'], 'viewer_recipient' => $user['id']]);
$message = $stmt->fetch();
if (!$message) {
    http_response_code(403);
    exit('Bu işlem için yetkiniz yok.');
}

if ((int) $message['recipient_id'] === (int) $user['id']) {
    $update = db()->prepare('UPDATE messages SET deleted_by_recipient=1 WHERE id=:id AND recipient_id=:viewer');
    $return = 'inbox.php';
} else {
    $update = db()->prepare('UPDATE messages SET deleted_by_sender=1 WHERE id=:id AND sender_id=:viewer');
    $return = 'sent.php';
}
$update->execute(['id' => $id, 'viewer' => $user['id']]);
flash('success', 'Mesaj çöp kutusuna taşındı.');
redirect($return);
