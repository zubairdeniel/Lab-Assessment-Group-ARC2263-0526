<?php
require_once __DIR__ . '/auth.php';
$_SESSION = [];
session_unset();
session_destroy();
header('Location: login.html');
exit;
