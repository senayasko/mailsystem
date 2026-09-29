<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Mesaj bulunamadı.');
}

// Kimlik numarası değiştirilse bile yalnızca gönderen veya alıcı mesajı görebilir.
$stmt = db()->prepare("SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) sender_name, s.email sender_email, CONCAT(r.first_name, ' ', r.last_name) recipient_name, r.email recipient_email FROM messages m JOIN users s ON s.id=m.sender_id JOIN users r ON r.id=m.recipient_id WHERE m.id=:message AND (m.sender_id=:viewer_sender OR m.recipient_id=:viewer_recipient) LIMIT 1");
$stmt->execute(['message' => $id, 'viewer_sender' => $user['id'], 'viewer_recipient' => $user['id']]);
$message = $stmt->fetch();
if (!$message) {
    http_response_code(404);
    render_app_start('Mesaj bulunamadı', '', $user);
    echo '<div class="empty-state"><span class="empty-icon">' . icon_svg('info') . '</span><h2>Mesaj bulunamadı</h2><p>Bu mesaj mevcut değil veya görüntüleme yetkin yok.</p><a class="primary-button inline-button" href="' . e(url('inbox.php')) . '">Gelen kutusuna dön</a></div>';
    render_app_end();
    exit;
}

$isRecipient = (int) $message['recipient_id'] === (int) $user['id'];
$isSender = (int) $message['sender_id'] === (int) $user['id'];
if ($isRecipient && !$message['is_read']) {
    $update = db()->prepare('UPDATE messages SET is_read=1 WHERE id=:id AND recipient_id=:recipient');
    $update->execute(['id' => $id, 'recipient' => $user['id']]);
    $message['is_read'] = 1;
}
$back = $isRecipient ? 'inbox.php' : 'sent.php';
render_app_start($message['subject'], $isRecipient ? 'inbox' : 'sent', $user);
?>
<div class="detail-toolbar">
    <a class="icon-button" href="<?= e(url($back)) ?>" aria-label="Geri dön"><?= icon_svg('back') ?></a>
    <span class="detail-spacer"></span>
    <a class="icon-button reply-button" href="<?= e(url('compose.php?reply_to=' . $message['id'])) ?>" aria-label="Bu mesajı yanıtla"><?= icon_svg('reply') ?><span>Yanıtla</span></a>
    <?php if ($isRecipient): ?>
        <form action="<?= e(url('actions/toggle_star.php')) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
            <button class="icon-button <?= $message['is_starred'] ? 'starred' : '' ?>" title="Yıldızı değiştir" aria-label="Yıldızı değiştir"><?= icon_svg('star') ?></button>
        </form>
    <?php endif; ?>
    <form action="<?= e(url('actions/delete_message.php')) ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
        <button class="icon-button" title="Çöp kutusuna taşı" aria-label="Çöp kutusuna taşı"><?= icon_svg('trash') ?></button>
    </form>
</div>
<article class="message-card">
    <header class="message-header">
        <h1><?= e($message['subject']) ?></h1>
        <div class="message-meta">
            <span class="mail-avatar large"><?= e(initial($message['sender_name'])) ?></span>
            <div><strong><?= e($message['sender_name']) ?></strong><small>&lt;<?= e($message['sender_email']) ?>&gt;</small><p>Alıcı: <?= e($message['recipient_name']) ?> &lt;<?= e($message['recipient_email']) ?>&gt;</p></div>
            <time datetime="<?= e($message['created_at']) ?>"><?= e(display_date($message['created_at'])) ?></time>
        </div>
    </header>
    <div class="message-body"><?= nl2br(e($message['body'])) ?></div>
</article>
<?php render_app_end(); ?>
