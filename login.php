<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_guest();
render_public_start(t('Giriş yap'));
?>
<div class="auth-card">
    <h2><?= e(t('Hesabına giriş yap')) ?></h2>
    <p class="muted"><?= e(t('Kullanıcı adın veya Posta adresinle devam et.')) ?></p>
    <form action="<?= e(url('actions/login.php')) ?>" method="post" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label><?= e(t('Kullanıcı adı veya mail adresi')) ?> <input name="identity" type="text" maxlength="80" placeholder="<?= e(t('senay veya senay@mailsystem.local')) ?>" required autofocus autocomplete="username">
        </label>
        <label><?= e(t('Şifre')) ?> <span class="password-field"><input name="password" type="password" required autocomplete="current-password" data-password><button type="button" data-password-toggle aria-label="<?= e(t('Şifreyi göster')) ?>"><?= e(t('Göster')) ?></button></span>
        </label>
        <button class="primary-button" type="submit"><span class="button-label"><?= e(t('Giriş yap')) ?></span><span class="spinner"></span></button>
    </form>
    <p class="auth-switch"><?= e(t('Hesabın yok mu?')) ?> <a href="<?= e(url('register.php')) ?>"><?= e(t('Hesap oluştur')) ?></a></p>
</div>
<?php render_public_end(); ?>
