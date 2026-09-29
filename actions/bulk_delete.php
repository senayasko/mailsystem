<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$user = require_auth();

$rawIds = $_POST['message_ids'] ?? [];
$ids = is_array($rawIds)
    ? array_values(array_unique(array_filter(array_map('intval', $rawIds), static fn(int $id): bool => $id > 0)))
    : [];
if (!$ids) {
    flash('error', 'Silmek için en az bir mesaj seçin.');
    redirect('inbox.php');
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$params = array_merge([$user['id'], $user['id']], $ids);
$check = db()->prepare("SELECT id, sender_id, recipient_id FROM messages WHERE (sender_id = ? OR recipient_id = ?) AND id IN ($placeholders)");
$check->execute($params);
$authorizedIds = array_column($check->fetchAll(), 'id');
if (!$authorizedIds) {
    http_response_code(403);
    exit('Bu mesajları silmek için yetkiniz yok.');
}

$authorizedPlaceholders = implode(',', array_fill(0, count($authorizedIds), '?'));
$recipientParams = array_merge([$user['id']], $authorizedIds);
$recipientDelete = db()->prepare("UPDATE messages SET deleted_by_recipient = 1 WHERE recipient_id = ? AND id IN ($authorizedPlaceholders)");
$recipientDelete->execute($recipientParams);
$senderParams = array_merge([$user['id']], $authorizedIds);
$senderDelete = db()->prepare("UPDATE messages SET deleted_by_sender = 1 WHERE sender_id = ? AND id IN ($authorizedPlaceholders)");
$senderDelete->execute($senderParams);

$returnTo = (string) ($_POST['return_to'] ?? 'inbox.php');
$allowedReturns = ['inbox.php', 'sent.php', 'starred.php', 'trash.php'];
flash('success', count($authorizedIds) . ' mesaj çöp kutusuna taşındı.');
redirect(in_array($returnTo, $allowedReturns, true) ? $returnTo : 'inbox.php');
