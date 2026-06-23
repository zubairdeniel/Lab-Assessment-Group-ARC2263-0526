<?php
/**
 * admin/auth.php
 * Session helpers for the admin portal. Include this at the top of any
 * admin page or endpoint that requires a logged-in admin.
 */

session_start();

/** Redirect to login if no admin session exists (for HTML page guards). */
function require_admin_session(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.html');
        exit;
    }
}

/** Return a 401 JSON error if no admin session exists (for API endpoints). */
function require_admin_api(): void {
    if (empty($_SESSION['admin_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated. Please log in.']);
        exit;
    }
}
