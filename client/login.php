<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$studentNum = trim($data['student_number'] ?? '');
$password = (string)($data['password'] ?? '');

if ($studentNum === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Student number and password required']);
    exit;
}

try {
    $db = getDB();
    $st = $db->prepare('SELECT id, student_number, first_name, last_name, password FROM students WHERE student_number = ?');
    $st->execute([$studentNum]);
    $student = $st->fetch();

    if (!$student || !password_verify($password, $student['password'] ?? '')) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['student_id'] = $student['id'];
    $_SESSION['student_number'] = $student['student_number'];
    $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];

    echo json_encode(['success' => true, 'redirect' => 'index.php']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
