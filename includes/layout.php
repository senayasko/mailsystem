<?php
declare(strict_types=1);

function render_public_start(string $title): void
{
    $flash = take_flash();
    ?>
    <!doctype html>
    <html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · Posta</title>
        <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
        <script defer src="<?= e(url('assets/js/app.js')) ?>"></script>
    </head>
    <body class="auth-body">
    <main class="auth-shell">
        <section class="auth-panel">
            <a class="brand auth-brand" href="<?= e(url()) ?>" aria-label="Posta ana sayfa">
                <span class="brand-mark"><?= icon_svg('mail') ?></span><span>Posta</span>
            </a>
            <?php render_flash($flash); ?>
    <?php
}

function render_public_end(): void
{
    echo '</section></main></body></html>';
}

function unread_count(int $userId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id = :id AND is_read = 0 AND deleted_by_recipient = 0');
    $stmt->execute(['id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function render_app_start(string $title, string $active, array $user): void
{
    $flash = take_flash();
    $unread = unread_count((int) $user['id']);
    ?>
    <!doctype html>
    <html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> · Posta</title>
        <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
        <script defer src="<?= e(url('assets/js/app.js')) ?>"></script>
    </head>
    <body class="app-body">
    <div class="app-shell">
        <aside class="sidebar" id="sidebar" aria-label="Ana menü">
            <div class="sidebar-header">
                <a class="brand" href="<?= e(url('inbox.php')) ?>"><span class="brand-mark"><?= icon_svg('mail') ?></span><span>Posta</span></a>
                <button class="sidebar-close" type="button" data-sidebar-close aria-label="Menüyü kapat"><?= icon_svg('close') ?></button>
            </div>
            <a class="compose-button" href="<?= e(url('compose.php')) ?>"><?= icon_svg('compose') ?><span>Yeni Mesaj</span></a>
            <nav class="main-nav" aria-label="Posta kutuları">
                <?php nav_link('inbox.php', 'Gelen Kutusu', 'inbox', $active, 'inbox', $unread ?: null); ?>
                <?php nav_link('starred.php', 'Yıldızlı', 'starred', $active, 'star'); ?>
                <?php nav_link('sent.php', 'Gönderilenler', 'sent', $active, 'sent'); ?>
                <?php nav_link('trash.php', 'Çöp Kutusu', 'trash', $active, 'trash'); ?>
            </nav>
            <div class="sidebar-account">
                <span class="avatar"><?= e(initial($user['first_name'])) ?></span>
                <span><strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong><small><?= e($user['email']) ?></small></span>
            </div>
            <form action="<?= e(url('actions/logout.php')) ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button class="logout-button" type="submit">Çıkış yap</button>
            </form>
        </aside>
        <section class="workspace">
            <header class="topbar">
                <button class="menu-button" type="button" data-sidebar-toggle aria-label="Menüyü aç"><?= icon_svg('menu') ?></button>
                <label class="search-box"><span class="sr-only">Mesajlarda ara</span><?= icon_svg('search') ?><input type="search" placeholder="Mesajlarda ara" data-mail-search autocomplete="off"></label>
                <div class="top-account"><span class="avatar"><?= e(initial($user['first_name'])) ?></span><span><?= e($user['username']) ?></span></div>
            </header>
            <main class="content">
                <?php render_flash($flash); ?>
    <?php
}

function render_app_end(): void
{
    echo '</main></section></div><div class="sidebar-backdrop" data-sidebar-toggle></div></body></html>';
}

function nav_link(string $path, string $label, string $key, string $active, string $icon, ?int $count = null): void
{
    $class = $key === $active ? 'nav-link active' : 'nav-link';
    $current = $key === $active ? ' aria-current="page"' : '';
    echo '<a class="' . $class . '" href="' . e(url($path)) . '"' . $current . '><span class="nav-icon">' . icon_svg($icon) . '</span><span>' . e($label) . '</span>';
    if ($count !== null) {
        echo '<strong class="nav-count">' . $count . '</strong>';
    }
    echo '</a>';
}

function render_flash(?array $flash): void
{
    if (!$flash) {
        return;
    }
    echo '<div class="alert alert-' . e($flash['type']) . '" role="alert">' . icon_svg($flash['type'] === 'success' ? 'check' : 'info') . '<span>' . e($flash['message']) . '</span></div>';
}

function icon_svg(string $name): string
{
    $paths = [
        'mail' => '<path d="M3 5.75h18v12.5H3z"/><path d="m4 7 8 6 8-6"/>',
        'compose' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'inbox' => '<path d="M4 4h16l2 9v7H2v-7Z"/><path d="M2 13h5l2 3h6l2-3h5"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9Z"/>',
        'sent' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'trash' => '<path d="M3 6h18"/><path d="M8 6V3h8v3M19 6l-1 15H6L5 6"/><path d="M10 11v5M14 11v5"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'refresh' => '<path d="M20 11a8 8 0 1 0 1 5"/><path d="M20 4v7h-7"/>',
        'back' => '<path d="m15 18-6-6 6-6"/>',
        'reply' => '<path d="m9 17-5-5 5-5"/><path d="M4 12h9a7 7 0 0 1 7 7v1"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['info']) . '</svg>';
}

function render_mailbox_toolbar(string $title, array $messages, string $path, bool $showUnread = false): void
{
    $unread = $showUnread ? count(array_filter($messages, static fn(array $message): bool => !(bool) $message['is_read'])) : 0;
    ?>
    <div class="mailbox-toolbar">
        <div class="toolbar-selection">
            <label class="checkbox-control" title="Tümünü seç">
                <input type="checkbox" data-select-all aria-label="Tüm mesajları seç">
                <span></span>
            </label>
            <span class="selection-status" data-selection-status><?= count($messages) ?> mesaj</span>
            <form class="bulk-actions" action="<?= e(url('actions/bulk_delete.php')) ?>" method="post" data-bulk-delete-form>
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="return_to" value="<?= e($path) ?>">
                <button class="toolbar-button bulk-delete" type="submit" data-bulk-delete disabled aria-label="Seçili mesajları sil" title="Seçili mesajları sil"><?= icon_svg('trash') ?></button>
            </form>
        </div>
        <div class="mailbox-title">
            <h1><?= e($title) ?></h1>
            <?php if ($showUnread): ?><span><?= $unread ?> okunmamış</span><?php endif; ?>
        </div>
        <a class="toolbar-button" href="<?= e(url($path)) ?>" aria-label="Mesaj listesini yenile" title="Yenile"><?= icon_svg('refresh') ?></a>
    </div>
    <?php
}

function render_message_list(array $messages, string $box): void
{
    if (!$messages) {
        $empty = [
            'inbox' => ['Gelen kutun boş', 'Yeni mesajlar burada görünecek.'],
            'sent' => ['Henüz mesaj göndermedin', 'İlk mesajını oluşturup sohbeti başlatabilirsin.'],
            'starred' => ['Yıldızlı mesaj yok', 'Önemli mesajlarını yıldızlayınca burada toplanacak.'],
            'trash' => ['Çöp kutusu boş', 'Sildiğin mesajlar burada görünecek.'],
        ][$box] ?? ['Burada mesaj yok', ''];
        echo '<div class="empty-state"><span class="empty-icon">' . icon_svg('inbox') . '</span><h2>' . e($empty[0]) . '</h2><p>' . e($empty[1]) . '</p>';
        if ($box === 'inbox' || $box === 'sent') {
            echo '<a href="' . e(url('compose.php')) . '">Yeni mesaj oluştur</a>';
        }
        echo '</div>';
        return;
    }
    echo '<div class="mail-list" data-mail-list>';
    foreach ($messages as $message) {
        $unreadClass = !$message['is_read'] && in_array($box, ['inbox', 'starred'], true) ? ' unread' : '';
        $showRecipient = $box === 'sent' || ($box === 'trash' && (int) $message['sender_id'] === (int) (current_user()['id'] ?? 0));
        $person = $showRecipient ? $message['recipient_name'] : $message['sender_name'];
        $address = $showRecipient ? $message['recipient_email'] : $message['sender_email'];
        echo '<div class="mail-row' . $unreadClass . '" data-mail-item data-search-text="' . e(mb_strtolower($person . ' ' . $address . ' ' . $message['subject'] . ' ' . $message['body'])) . '">';
        echo '<label class="checkbox-control row-checkbox"><input type="checkbox" data-row-select data-message-id="' . (int) $message['id'] . '" value="' . (int) $message['id'] . '" aria-label="Mesajı seç"><span></span></label>';
        if (in_array($box, ['inbox', 'starred'], true)) {
            echo '<form class="row-star" action="' . e(url('actions/toggle_star.php')) . '" method="post"><input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '"><input type="hidden" name="message_id" value="' . (int) $message['id'] . '"><input type="hidden" name="return_to" value="' . e($box . '.php') . '"><button class="star-button' . ($message['is_starred'] ? ' is-starred' : '') . '" type="submit" aria-label="' . ($message['is_starred'] ? 'Yıldızı kaldır' : 'Yıldızla') . '">' . icon_svg('star') . '</button></form>';
        } else {
            echo '<span class="row-star-placeholder">' . icon_svg('star') . '</span>';
        }
        echo '<a class="mail-row-link" href="' . e(url('message.php?id=' . $message['id'])) . '">';
        echo '<span class="mail-avatar">' . e(initial($person)) . '</span>';
        echo '<span class="mail-sender"><strong>' . e($person) . '</strong><small>' . e($address) . '</small></span>';
        echo '<span class="mail-copy"><strong>' . e($message['subject']) . '</strong><small>' . e(preview($message['body'])) . '</small></span>';
        echo '<time datetime="' . e($message['created_at']) . '">' . e(display_date($message['created_at'])) . '</time>';
        echo '</a></div>';
    }
    echo '<div class="search-empty" data-search-empty hidden>Aramana uyan mesaj bulunamadı.</div></div>';
}
