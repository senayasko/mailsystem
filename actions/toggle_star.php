<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$user = require_auth();
$id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
if (!$id) {
    if (wants_json()) json_response(['error' => t('Mesaj bulunamadı.')], 422);
    redirect('inbox.php');
}
$stmt = db()->prepare('UPDATE messages SET is_starred = 1 - is_starred WHERE id=:id AND recipient_id=:recipient');
$stmt->execute(['id' => $id, 'recipient' => $user['id']]);
if ($stmt->rowCount() === 0) {
    if (wants_json()) json_response(['error' => t('Bu işlem için yetkiniz yok.')], 403);
    http_response_code(403);
    exit(t('Bu işlem için yetkiniz yok.'));
}
if (wants_json()) {
    $state = db()->prepare('SELECT is_starred FROM messages WHERE id = :id AND recipient_id = :recipient');
    $state->execute(['id' => $id, 'recipient' => $user['id']]);
    json_response(['id' => $id, 'is_starred' => (bool) $state->fetchColumn(), 'unread_count' => unread_count((int) $user['id'])]);
}
$returnTo = (string) ($_POST['return_to'] ?? '');
$allowedReturns = ['inbox.php', 'starred.php'];
redirect(in_array($returnTo, $allowedReturns, true) ? $returnTo : 'message.php?id=' . $id);
