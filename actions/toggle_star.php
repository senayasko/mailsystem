<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$user = require_auth();
$id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect('inbox.php');
}
$stmt = db()->prepare('UPDATE messages SET is_starred = NOT is_starred WHERE id=:id AND recipient_id=:recipient');
$stmt->execute(['id' => $id, 'recipient' => $user['id']]);
if ($stmt->rowCount() === 0) {
    http_response_code(403);
    exit('Bu işlem için yetkiniz yok.');
}
$returnTo = (string) ($_POST['return_to'] ?? '');
$allowedReturns = ['inbox.php', 'starred.php'];
redirect(in_array($returnTo, $allowedReturns, true) ? $returnTo : 'message.php?id=' . $id);
