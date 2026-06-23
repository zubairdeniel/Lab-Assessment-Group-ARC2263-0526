<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$username = trim($data['username'] ?? '');
$password = (string)($data['password'] ?? '');

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit;
}

try {
    $db = getDB();
    $st = $db->prepare('SELECT * FROM admins WHERE username = ?');
    $st->execute([$username]);
    $admin = $st->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];

    echo json_encode(['success' => true, 'redirect' => 'index.php']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
