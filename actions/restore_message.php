<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$user = require_auth();

$rawIds = $_POST['message_ids'] ?? [$_POST['message_id'] ?? 0];
$ids = is_array($rawIds)
    ? array_values(array_unique(array_filter(array_map(static fn($id): int => is_scalar($id) ? (int) $id : 0, $rawIds), static fn(int $id): bool => $id > 0)))
    : [];
if (!$ids) {
    flash('error', t('Geri almak için en az bir mesaj seçin.'));
    redirect('trash.php');
}

// Yalnızca isteği yapan kullanıcının silme işaretini kaldır.
// Diğer tarafın çöp kutusu tercihini ve mesajın okunma/yıldız durumunu koru.
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$query = db()->prepare("UPDATE messages SET
    deleted_by_recipient = CASE WHEN recipient_id = ? THEN 0 ELSE deleted_by_recipient END,
    deleted_by_sender = CASE WHEN sender_id = ? THEN 0 ELSE deleted_by_sender END
    WHERE id IN ($placeholders) AND
    ((recipient_id = ? AND deleted_by_recipient = 1) OR (sender_id = ? AND deleted_by_sender = 1))");
$query->execute(array_merge([$user['id'], $user['id']], $ids, [$user['id'], $user['id']]));
$count = $query->rowCount();
if ($count === 0) {
    http_response_code(403);
    exit(t('Geri alınabilecek mesaj bulunamadı veya bu işlem için yetkiniz yok.'));
}
flash('success', t('{count} mesaj çöp kutusundan geri alındı.', ['count' => $count]));
redirect('trash.php');
