<?php
require_once __DIR__ . '/includes/bootstrap.php';
redirect(current_user() ? 'inbox.php' : 'login.php');
