<?php
require_once __DIR__ . '/../includes/bootstrap.php';
only_post();
verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
flash('success', 'Güvenli şekilde çıkış yaptınız.');
redirect('login.php');
