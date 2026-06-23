<?php
session_start();

function require_student_session(): void {
    if (empty($_SESSION['student_id'])) {
        header('Location: login.html');
        exit;
    }
}

function require_student_api(): void {
    if (empty($_SESSION['student_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
}
