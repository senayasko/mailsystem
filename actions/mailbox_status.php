<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    json_response(['error' => 'GET required'], 405);
}
$user = require_auth();
session_write_close();
json_response(['unread_count' => unread_count((int) $user['id'])]);
