<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$stmt = db()->prepare("SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) sender_name, s.email sender_email, CONCAT(r.first_name, ' ', r.last_name) recipient_name, r.email recipient_email FROM messages m JOIN users s ON s.id=m.sender_id JOIN users r ON r.id=m.recipient_id WHERE m.recipient_id=:id AND m.deleted_by_recipient=0 ORDER BY m.created_at DESC");
$stmt->execute(['id' => $user['id']]);
$messages = $stmt->fetchAll();
render_app_start('Gelen Kutusu', 'inbox', $user);
?>
<?php render_mailbox_toolbar('Gelen Kutusu', $messages, 'inbox.php', true); ?>
<?php render_message_list($messages, 'inbox'); render_app_end(); ?>
