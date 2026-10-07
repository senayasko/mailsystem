<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit(t('Mesaj bulunamadı.'));
}

// Kimlik numarası değiştirilse bile yalnızca gönderen veya alıcı mesajı görebilir.
$stmt = db()->prepare("SELECT m.*, CONCAT(s.first_name, ' ', s.last_name) sender_name, s.email sender_email, CONCAT(r.first_name, ' ', r.last_name) recipient_name, r.email recipient_email FROM messages m JOIN users s ON s.id=m.sender_id JOIN users r ON r.id=m.recipient_id WHERE m.id=:message AND (m.sender_id=:viewer_sender OR m.recipient_id=:viewer_recipient) LIMIT 1");
$stmt->execute(['message' => $id, 'viewer_sender' => $user['id'], 'viewer_recipient' => $user['id']]);
$message = $stmt->fetch();
if (!$message) {
    http_response_code(404);
    render_app_start(t('Mesaj bulunamadı'), '', $user);
    echo '<div class="empty-state"><span class="empty-icon">' . icon_svg('info') . '</span><h2>' . e(t('Mesaj bulunamadı')) . '</h2><p>' . e(t('Bu mesaj mevcut değil veya görüntüleme yetkin yok.')) . '</p><a class="primary-button inline-button" href="' . e(url('inbox.php')) . '">' . e(t('Gelen kutusuna dön')) . '</a></div>';
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
require_once __DIR__ . '/includes/attachments.php';
$attachmentQuery = db()->prepare('SELECT id, original_name, size_bytes FROM attachments WHERE message_id = :message');
$attachmentQuery->execute(['message' => $message['id']]);
$attachment = $attachmentQuery->fetch();
render_app_start($message['subject'], $isRecipient ? 'inbox' : 'sent', $user);
?>
<div class="detail-toolbar">
    <a class="icon-button" href="<?= e(url($back)) ?>" aria-label="<?= e(t('Geri dön')) ?>"><?= icon_svg('back') ?></a>
    <span class="detail-spacer"></span>
    <a class="icon-button reply-button" href="<?= e(url('compose.php?reply_to=' . $message['id'])) ?>" aria-label="<?= e(t('Bu mesajı yanıtla')) ?>"><?= icon_svg('reply') ?><span><?= e(t('Yanıtla')) ?></span></a>
    <?php if ($isRecipient): ?>
        <form data-star-form action="<?= e(url('actions/toggle_star.php')) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
            <button class="icon-button <?= $message['is_starred'] ? 'starred' : '' ?>" aria-pressed="<?= $message['is_starred'] ? 'true' : 'false' ?>" title="<?= e(t('Yıldızı değiştir')) ?>" aria-label="<?= e(t($message['is_starred'] ? 'Yıldızı kaldır' : 'Yıldızla')) ?>"><?= icon_svg('star') ?></button>
        </form>
    <?php endif; ?>
    <form action="<?= e(url('actions/delete_message.php')) ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
        <button class="icon-button" title="<?= e(t('Çöp kutusuna taşı')) ?>" aria-label="<?= e(t('Çöp kutusuna taşı')) ?>"><?= icon_svg('trash') ?></button>
    </form>
</div>
<article class="message-card">
    <header class="message-header">
        <h1><?= e($message['subject']) ?></h1>
        <div class="message-meta">
            <span class="mail-avatar large"><?= e(initial($message['sender_name'])) ?></span>
            <div><strong><?= e($message['sender_name']) ?></strong><small>&lt;<?= e($message['sender_email']) ?>&gt;</small><p><?= e(t('Alıcı:')) ?> <?= e($message['recipient_name']) ?> &lt;<?= e($message['recipient_email']) ?>&gt;</p></div>
            <time datetime="<?= e($message['created_at']) ?>"><?= e(display_date($message['created_at'])) ?></time>
        </div>
    </header>
    <div class="message-body"><?= nl2br(e($message['body'])) ?></div>
    <?php if ($attachment): ?>
        <div class="message-attachment">
            <a href="<?= e(url('download.php?id=' . $attachment['id'])) ?>" aria-label="<?= e(t('İndir') . ': ' . $attachment['original_name']) ?>">
                <strong><?= e($attachment['original_name']) ?></strong>
                <span><?= e(attachment_size((int) $attachment['size_bytes'])) ?> · <?= e(t('İndir')) ?></span>
            </a>
        </div>
    <?php endif; ?>
</article>
<?php render_app_end(); ?>
