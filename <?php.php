<?php
require_once __DIR__ . '/auth.php';
require_student_api();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$studentId = $_SESSION['student_id'];

try {
    $db = getDB();
    match ($action) {
        'profile' => getStudentProfile($db, $studentId),
        default => json(['success' => false, 'message' => 'Unknown action'], 400),
    };
} catch (Throwable $e) {
    json(['success' => false, 'message' => 'Server error'], 500);
}

function getStudentProfile(PDO $db, int $studentId): void {
    $st = $db->prepare('SELECT * FROM students WHERE id = ?');
    $st->execute([$studentId]);
    $student = $st->fetch();

    if (!$student) {
        json(['success' => false, 'message' => 'Student not found'], 404);
        return;
    }

    json(['success' => true, 'data' => $student]);
}

function json(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
