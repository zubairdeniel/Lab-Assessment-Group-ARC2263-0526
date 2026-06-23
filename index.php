<?php
// Get the current URL path
$request = $_SERVER['REQUEST_URI'];

// Clean up trailing slashes
$request = rtrim($request, '/');

// Route Admin requests
if (strpos($request, '/admin') === 0) {
    $file = __DIR__ . $request;
    if (is_dir($file)) {
        include __DIR__ . '/admin/login.php';
    } elseif (file_exists($file)) {
        include $file;
    } else {
        include __DIR__ . '/admin/login.php';
    }
    exit;
}

// Route Client/Student requests
$file = __DIR__ . '/client' . $request;
if (empty($request) || $request === '') {
    include __DIR__ . '/client/login.html';
} elseif (file_exists($file)) {
    include $file;
} else {
    include __DIR__ . '/client/login.html';
}
exit;