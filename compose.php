<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_auth();
$old = $_SESSION['old_compose'] ?? [];
unset($_SESSION['old_compose']);
$replyId = filter_input(INPUT_GET, 'reply_to', FILTER_VALIDATE_INT);
$prefill = [];
$closePath = 'inbox.php';
$composeTitle = 'Yeni Mesaj';

if ($replyId) {
    // Yanıt verilecek mesajı her istekte yeniden yetkilendir.
    $stmt = db()->prepare("SELECT m.subject, m.sender_id, m.recipient_id, s.email sender_email, r.email recipient_email FROM messages m JOIN users s ON s.id=m.sender_id JOIN users r ON r.id=m.recipient_id WHERE m.id=:message AND (m.sender_id=:viewer_sender OR m.recipient_id=:viewer_recipient) LIMIT 1");
    $stmt->execute([
        'message' => $replyId,
        'viewer_sender' => $user['id'],
        'viewer_recipient' => $user['id'],
    ]);
    $replyMessage = $stmt->fetch();
    if (!$replyMessage) {
        flash('error', 'Yanıtlanacak mesaj bulunamadı veya bu mesaj için yetkiniz yok.');
        redirect('compose.php');
    }

    $recipient = (int) $replyMessage['sender_id'] === (int) $user['id']
        ? $replyMessage['recipient_email']
        : $replyMessage['sender_email'];
    $subject = preg_match('/^\s*re\s*:/iu', $replyMessage['subject'])
        ? $replyMessage['subject']
        : 'Re: ' . mb_substr($replyMessage['subject'], 0, 176, 'UTF-8');
    $subject = mb_substr($subject, 0, 180, 'UTF-8');
    $prefill = ['recipient' => $recipient, 'subject' => $subject, 'body' => ''];
    $closePath = 'message.php?id=' . $replyId;
    $composeTitle = 'Yanıtla';
}

$values = array_merge($prefill, $old);
render_app_start($composeTitle, 'compose', $user);
?>
<section class="compose-card" aria-labelledby="compose-title">
    <header class="compose-header">
        <h1 id="compose-title"><?= e($composeTitle) ?></h1>
        <a class="icon-button" href="<?= e(url($closePath)) ?>" aria-label="Mesaj yazma ekranını kapat"><?= icon_svg('close') ?></a>
    </header>
    <form action="<?= e(url('actions/send_message.php')) ?>" method="post" class="compose-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label><span>Alıcı</span><input name="recipient" maxlength="80" value="<?= e($values['recipient'] ?? '') ?>" placeholder="kullanici veya kullanici@mailsystem.local" required autofocus></label>
        <label><span>Konu</span><input name="subject" maxlength="180" value="<?= e($values['subject'] ?? '') ?>" placeholder="Mesajın konusu" required></label>
        <label class="compose-body"><span>Mesaj</span><textarea name="body" maxlength="20000" placeholder="Mesajını yaz…" required><?= e($values['body'] ?? '') ?></textarea><small><span data-char-count>0</span> / 20.000</small></label>
        <div class="compose-actions"><button class="primary-button" type="submit"><span class="button-label">Gönder</span><span class="spinner"></span><?= icon_svg('sent') ?></button><a href="<?= e(url($closePath)) ?>">Vazgeç</a></div>
    </form>
</section>
<?php render_app_end(); ?>
