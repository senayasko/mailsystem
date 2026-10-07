<?php
declare(strict_types=1);

const ATTACHMENT_MAX_BYTES = 2 * 1024 * 1024;

function attachment_directory(): string
{
    return __DIR__ . '/../storage/attachments';
}

// Tarayıcının bildirdiği tür yerine dosyanın içeriğini ve uzantısını kontrol et.
function validate_attachment(?array $file): ?array
{
    if ($file === null || ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (!isset($file['error'], $file['name'], $file['tmp_name'], $file['size'])
        || !is_int($file['error']) || !is_string($file['name'])
        || !is_string($file['tmp_name']) || !is_int($file['size'])) {
        throw new RuntimeException(t('Dosya yükleme isteği geçersiz.'));
    }
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        throw new RuntimeException(t('Dosya en fazla 2 MB olabilir.'));
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException(t('Dosya yüklenemedi. Lütfen tekrar seçin.'));
    }
    $size = filesize($file['tmp_name']);
    if ($size === false || $size < 1 || $size > ATTACHMENT_MAX_BYTES) {
        throw new RuntimeException(t('Boş dosya gönderilemez. Dosya en fazla 2 MB olabilir.'));
    }
    $name = basename(str_replace('\\', '/', $file['name']));
    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
    if (!$name || mb_strlen($name, 'UTF-8') > 180) {
        throw new RuntimeException(t('Dosya adı geçersiz veya çok uzun.'));
    }
    $allowed = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'txt' => 'text/plain'];
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$extension]) || $mime !== $allowed[$extension]) {
        throw new RuntimeException(t('Yalnızca PDF, JPG, PNG veya TXT dosyası ekleyebilirsin. Dosyanın içeriği uzantısıyla eşleşmeli.'));
    }
    return ['original_name' => $name, 'stored_name' => bin2hex(random_bytes(24)) . '.bin', 'mime_type' => $mime, 'size_bytes' => $size, 'tmp_name' => $file['tmp_name']];
}

function attachment_size(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }
    return $bytes >= 1024 * 1024 ? number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB' : number_format($bytes / 1024, 1, ',', '.') . ' KB';
}
