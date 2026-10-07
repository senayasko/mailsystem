<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$language = $_POST['language'] ?? '';
if (in_array($language, ['tr', 'en'], true)) {
    $_SESSION['language'] = $language;
}
// Yönlendirmeyi yalnızca uygulamanın bilinen sayfalarıyla sınırla.
$return = is_string($_POST['return_to'] ?? null) ? $_POST['return_to'] : '';
if (!preg_match('/^(index|login|register|inbox|sent|starred|trash|compose|message)\.php(?:\?(?:id|reply_to)=[1-9][0-9]*)?$/D', $return)) {
    $return = current_user() ? 'inbox.php' : 'login.php';
}
if (str_starts_with($return, 'compose.php') && current_user() && isset($_POST['compose_body'])) {
    $old = [];
    foreach (['recipient' => 80, 'subject' => 180, 'body' => 20000] as $field => $limit) {
        $value = $_POST['compose_' . $field] ?? '';
        $old[$field] = is_string($value) ? mb_substr($value, 0, $limit, 'UTF-8') : '';
    }
    $_SESSION['old_compose'] = $old;
}
redirect($return);
