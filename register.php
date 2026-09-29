<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_guest();
$old = $_SESSION['old_register'] ?? [];
unset($_SESSION['old_register']);
render_public_start('Hesap oluştur');
?>
<div class="auth-card auth-card-wide">
    <h2>Yeni hesap oluştur</h2>
    <p class="muted">Kullanıcı adın, platform içindeki mail adresin olacak.</p>
    <form action="<?= e(url('actions/register.php')) ?>" method="post" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
            <label>Ad<input name="first_name" maxlength="80" value="<?= e($old['first_name'] ?? '') ?>" required autofocus autocomplete="given-name"></label>
            <label>Soyad<input name="last_name" maxlength="80" value="<?= e($old['last_name'] ?? '') ?>" required autocomplete="family-name"></label>
        </div>
        <label>Kullanıcı adı
            <span class="address-field"><input name="username" minlength="3" maxlength="30" pattern="[a-z0-9._-]+" value="<?= e($old['username'] ?? '') ?>" required autocomplete="username"><span>@mailsystem.local</span></span>
            <small>Küçük harf, rakam, nokta, alt çizgi veya tire kullanabilirsin.</small>
        </label>
        <div class="form-grid">
            <label>Şifre<input name="password" type="password" minlength="8" maxlength="72" required autocomplete="new-password"></label>
            <label>Şifre tekrar<input name="password_repeat" type="password" minlength="8" maxlength="72" required autocomplete="new-password"></label>
        </div>
        <button class="primary-button" type="submit"><span class="button-label">Hesabımı oluştur</span><span class="spinner"></span></button>
    </form>
    <p class="auth-switch">Zaten hesabın var mı? <a href="<?= e(url('login.php')) ?>">Giriş yap</a></p>
</div>
<?php render_public_end(); ?>
