<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$language = current_language();
$_SESSION = ['language' => $language];
session_regenerate_id(true);
flash('success', t('Güvenli şekilde çıkış yaptınız.'));
redirect('login.php');
