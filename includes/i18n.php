<?php
declare(strict_types=1);

function current_language(): string
{
    return ($_SESSION['language'] ?? 'tr') === 'en' ? 'en' : 'tr';
}

// Türkçe metinler aynı zamanda sözlük anahtarıdır. Kullanıcı mesajları çevrilmez.
function t(string $text, array $values = []): string
{
    static $english = [
        'Giriş yap' => 'Sign in',
        'Hesap oluştur' => 'Create account',
        'Hesabına giriş yap' => 'Sign in to Posta',
        'Kullanıcı adın veya Posta adresinle devam et.' => 'Use your username or Posta address.',
        'Kullanıcı adı veya mail adresi' => 'Username or email address',
        'senay veya senay@mailsystem.local' => 'senay or senay@mailsystem.local',
        'Şifre' => 'Password',
        'Şifreyi göster' => 'Show password',
        'Şifreyi gizle' => 'Hide password',
        'Göster' => 'Show',
        'Gizle' => 'Hide',
        'Hesabın yok mu?' => 'New to Posta?',
        'Yeni hesap oluştur' => 'Create an account',
        'Kullanıcı adın, platform içindeki mail adresin olacak.' => 'Your username becomes your address on this platform.',
        'Ad' => 'First name',
        'Soyad' => 'Last name',
        'Kullanıcı adı' => 'Username',
        'Küçük harf, rakam, nokta, alt çizgi veya tire kullanabilirsin.' => 'Use lowercase letters, numbers, dots, underscores or hyphens.',
        'Şifre tekrar' => 'Confirm password',
        'Hesabımı oluştur' => 'Create my account',
        'Zaten hesabın var mı?' => 'Already have an account?',
        'Yeni Mesaj' => 'Compose',
        'Gelen Kutusu' => 'Inbox',
        'Yıldızlı' => 'Starred',
        'Gönderilenler' => 'Sent',
        'Çöp Kutusu' => 'Trash',
        'Posta ana sayfa' => 'Posta home',
        'Ana menü' => 'Main menu',
        'Menüyü kapat' => 'Close menu',
        'Menüyü aç' => 'Open menu',
        'Posta kutuları' => 'Mailboxes',
        'Çıkış yap' => 'Sign out',
        'Mesajlarda ara' => 'Search messages',
        'Tümünü seç' => 'Select all',
        'Tüm mesajları seç' => 'Select all messages',
        'Seçili mesajları sil' => 'Delete selected messages',
        'Mesaj listesini yenile' => 'Refresh messages',
        'Yenile' => 'Refresh',
        'Mesajı seç' => 'Select message',
        'Yıldızı kaldır' => 'Remove star',
        'Yıldızla' => 'Add star',
        'Yıldızı değiştir' => 'Toggle star',
        'Gelen kutun boş' => 'Your inbox is empty',
        'Yeni mesajlar burada görünecek.' => 'New messages will appear here.',
        'Henüz mesaj göndermedin' => 'No sent messages',
        'İlk mesajını oluşturup sohbeti başlatabilirsin.' => 'Compose your first message to get started.',
        'Yıldızlı mesaj yok' => 'No starred messages',
        'Önemli mesajlarını yıldızlayınca burada toplanacak.' => 'Messages you star will appear here.',
        'Çöp kutusu boş' => 'Your trash is empty',
        'Sildiğin mesajlar burada görünecek.' => 'Deleted messages will appear here.',
        'Burada mesaj yok' => 'No messages here',
        'Yeni mesaj oluştur' => 'Compose a message',
        'Aramana uyan mesaj bulunamadı.' => 'No messages match your search.',
        'Yanıtla' => 'Reply',
        'Mesaj yazma ekranını kapat' => 'Close compose',
        'Alıcı' => 'To',
        'Konu' => 'Subject',
        'Mesaj' => 'Message',
        'kullanici veya kullanici@mailsystem.local' => 'username or username@mailsystem.local',
        'Mesajın konusu' => 'Message subject',
        'Mesajını yaz…' => 'Write your message…',
        'Dosya eki' => 'Attachment',
        'İsteğe bağlı · PDF, JPG, PNG, TXT · En fazla 2 MB' => 'Optional · PDF, JPG, PNG, TXT · Up to 2 MB',
        'Gönder' => 'Send',
        'Vazgeç' => 'Cancel',
        'Geri dön' => 'Back',
        'Bu mesajı yanıtla' => 'Reply to this message',
        'Çöp kutusuna taşı' => 'Move to trash',
        'Alıcı:' => 'To:',
        'İndir' => 'Download',
        'Mesaj bulunamadı.' => 'Message not found.',
        'Mesaj bulunamadı' => 'Message not found',
        'Bu mesaj mevcut değil veya görüntüleme yetkin yok.' => 'This message does not exist or you do not have access.',
        'Gelen kutusuna dön' => 'Back to inbox',
        'Dosya bulunamadı veya indirme yetkin yok.' => 'File not found or you do not have permission to download it.',
        'Dosya bulunamadı.' => 'File not found.',
        'Kullanıcı adınızı ve şifrenizi girin.' => 'Enter your username and password.',
        'Kullanıcı adı veya şifre hatalı.' => 'Incorrect username or password.',
        'Ad ve soyad 1–80 karakter olmalıdır.' => 'First and last names must be 1–80 characters long.',
        'Kullanıcı adı 3–30 karakter olmalı; yalnızca küçük harf, rakam, nokta, alt çizgi ve tire içermelidir.' => 'Username must be 3–30 characters: lowercase letters, numbers, dots, underscores or hyphens.',
        'Şifre 8–72 karakter olmalıdır.' => 'Password must be 8–72 characters long.',
        'Şifreler eşleşmiyor.' => 'Passwords do not match.',
        'Bu kullanıcı adı zaten kullanılıyor.' => 'This username is already taken.',
        'Hesabınız oluşturuldu. Şimdi giriş yapabilirsiniz.' => 'Account created. You can now sign in.',
        'Güvenli şekilde çıkış yaptınız.' => 'You have signed out.',
        'Devam etmek için giriş yapın.' => 'Sign in to continue.',
        'Alıcı için geçerli bir kullanıcı adı veya @mailsystem.local adresi girin.' => 'Enter a valid username or @mailsystem.local address.',
        'Konu 1–180 karakter olmalıdır.' => 'Subject must be 1–180 characters long.',
        'Mesaj 1–20.000 karakter olmalıdır.' => 'Message must be 1–20,000 characters long.',
        'Alıcı bulunamadı. Sisteme kayıtlı bir kullanıcı adı girin.' => 'Recipient not found. Enter a registered username.',
        'Mesaj gönderildi. Gönderilenler bölümünde görebilirsiniz.' => 'Message sent. You can find it in Sent.',
        'Yanıtlanacak mesaj bulunamadı veya bu mesaj için yetkiniz yok.' => 'Message not found or you do not have permission to reply.',
        'Dosya yükleme isteği geçersiz.' => 'Invalid upload request.',
        'Dosya en fazla 2 MB olabilir.' => 'File must be 2 MB or smaller.',
        'Dosya en fazla 2 MB olabilir. Daha küçük bir dosya seçin.' => 'File must be 2 MB or smaller. Choose a smaller file.',
        'Dosya yüklenemedi. Lütfen tekrar seçin.' => 'Upload failed. Please select the file again.',
        'Boş dosya gönderilemez. Dosya en fazla 2 MB olabilir.' => 'Empty files are not allowed. Maximum file size is 2 MB.',
        'Dosya adı geçersiz veya çok uzun.' => 'Invalid filename or filename too long.',
        'Yalnızca PDF, JPG, PNG veya TXT dosyası ekleyebilirsin. Dosyanın içeriği uzantısıyla eşleşmeli.' => 'Only PDF, JPG, PNG or TXT files are allowed. File contents must match the extension.',
        'Dosyayı yeniden seçmen gerekecek.' => 'Please select your file again.',
        'Mesaj kaydedilemedi. Lütfen tekrar dene; ek varsa dosyayı yeniden seç.' => 'Message could not be saved. Try again and select your attachment again.',
        'Silmek için en az bir mesaj seçin.' => 'Select at least one message to delete.',
        'Bu mesajları silmek için yetkiniz yok.' => 'You do not have permission to delete these messages.',
        'Bu işlem için yetkiniz yok.' => 'You do not have permission for this action.',
        'Mesaj çöp kutusuna taşındı.' => 'Message moved to trash.',
        '{count} mesaj çöp kutusuna taşındı.' => '{count} messages moved to trash.',
        '{count} mesaj' => '{count} messages',
        '{count} seçili' => '{count} selected',
        '{count} okunmamış' => '{count} unread',
        'İstek doğrulanamadı. Sayfayı yenileyip tekrar deneyin.' => 'Request could not be verified. Refresh the page and try again.',
        'Bu işlem için POST isteği gereklidir.' => 'This action requires a POST request.',
        'İşlem tamamlanamadı. Tekrar dene.' => 'Could not complete the action. Try again.',
        'Oturumun sona erdi. Tekrar giriş yap.' => 'Your session has expired. Sign in again.',
        'Dil' => 'Language',
    ];
    $translated = current_language() === 'en' ? ($english[$text] ?? $text) : $text;
    foreach ($values as $key => $value) {
        $translated = str_replace('{' . $key . '}', (string) $value, $translated);
    }
    return $translated;
}

function render_language_switch(): void
{
    $page = basename($_SERVER['SCRIPT_NAME'] ?? 'inbox.php');
    $query = [];
    foreach (['id', 'reply_to'] as $key) {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
        if ($value && $value > 0) $query[$key] = $value;
    }
    $return = $page . ($query ? '?' . http_build_query($query) : '');
    ?>
    <form class="language-switch" action="<?= e(url('actions/language.php')) ?>" method="post" aria-label="<?= e(t('Dil')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="return_to" value="<?= e($return) ?>">
        <button type="submit" name="language" value="tr" aria-label="Türkçe" aria-pressed="<?= current_language() === 'tr' ? 'true' : 'false' ?>">TR</button>
        <button type="submit" name="language" value="en" aria-label="English" aria-pressed="<?= current_language() === 'en' ? 'true' : 'false' ?>">EN</button>
    </form>
    <?php
}

function render_client_translations(): void
{
    $strings = [];
    foreach (['{count} mesaj', '{count} seçili', '{count} okunmamış', 'Göster', 'Gizle', 'Şifreyi göster', 'Şifreyi gizle', 'Yıldızı kaldır', 'Yıldızla', 'Yıldızlı mesaj yok', 'Aramana uyan mesaj bulunamadı.', 'Dosya en fazla 2 MB olabilir.', 'İşlem tamamlanamadı. Tekrar dene.', 'Oturumun sona erdi. Tekrar giriş yap.'] as $key) {
        $strings[$key] = t($key);
    }
    echo '<script type="application/json" id="ui-translations">' . json_encode($strings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) . '</script>';
}
