<?php
require_once __DIR__ . '/auth.php';
require_admin_api();  // Check auth for API
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $db = getDB();
    match ($action) {
        'list'   => adminList($db),
        'create' => adminCreate($db),
        'update' => adminUpdate($db),
        'delete' => adminDelete($db),
        default  => jsonError('Unknown action', 400),
    };
} catch (PDOException $e) {
    jsonError('Database error', 500);
} catch (Throwable $e) {
    jsonError('Server error', 500);
}

function adminList(PDO $db): void {
    $search = trim($_GET['search'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
    $offset = ($page - 1) * $perPage;

    $where = [];
    $params = [];

    if ($search !== '') {
        $like = "%{$search}%";
        $where[] = '(CONCAT(first_name," ",last_name) LIKE ? OR student_number LIKE ?)';
        $params = array_merge($params, [$like, $like]);
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $countSql = "SELECT COUNT(*) FROM students $whereSql";
    $dataSql = "SELECT id, student_number, first_name, last_name, email, phone, course, year_level, gpa, status FROM students $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";

    $st = $db->prepare($countSql);
    $st->execute($params);
    $total = (int)$st->fetchColumn();

    $st2 = $db->prepare($dataSql);
    $st2->execute($params);
    $rows = $st2->fetchAll();

    json(['success' => true, 'total' => $total, 'page' => $page, 'data' => $rows]);
}

function adminCreate(PDO $db): void {
    $data = readBody();
    $errs = validateStudent($data, $db);
    if ($errs) { jsonError(implode('; ', $errs), 422); return; }

    $sql = "INSERT INTO students
              (student_number, first_name, last_name, dob, gender, address,
               email, phone, course, year_level, status)
            VALUES
              (:student_number,:first_name,:last_name,:dob,:gender,:address,
               :email,:phone,:course,:year_level,:status)";

    $fields = [
        'student_number' => trim($data['student_number'] ?? ''),
        'first_name'     => trim($data['first_name'] ?? ''),
        'last_name'      => trim($data['last_name'] ?? ''),
        'dob'            => $data['dob'] ?? null,
        'gender'         => trim($data['gender'] ?? ''),
        'address'        => trim($data['address'] ?? ''),
        'email'          => strtolower(trim($data['email'] ?? '')),
        'phone'          => trim($data['phone'] ?? ''),
        'course'         => trim($data['course'] ?? ''),
        'year_level'     => (int)($data['year_level'] ?? 1),
        'status'         => trim($data['status'] ?? 'Active'),
    ];

    $st = $db->prepare($sql);
    $st->execute($fields);
    json(['success' => true, 'id' => (int)$db->lastInsertId()]);
}

function adminUpdate(PDO $db): void {
    $data = readBody();
    if (empty($data['id'])) { jsonError('Missing id', 400); return; }

    $errs = validateStudent($data, $db, (int)$data['id']);
    if ($errs) { jsonError(implode('; ', $errs), 422); return; }

    $sql = "UPDATE students SET
               student_number = :student_number,
               first_name = :first_name,
               last_name = :last_name,
               dob = :dob,
               gender = :gender,
               address = :address,
               email = :email,
               phone = :phone,
               course = :course,
               year_level = :year_level,
               status = :status
            WHERE id = :id";

    $fields = [
        'id'             => (int)$data['id'],
        'student_number' => trim($data['student_number'] ?? ''),
        'first_name'     => trim($data['first_name'] ?? ''),
        'last_name'      => trim($data['last_name'] ?? ''),
        'dob'            => $data['dob'] ?? null,
        'gender'         => trim($data['gender'] ?? ''),
        'address'        => trim($data['address'] ?? ''),
        'email'          => strtolower(trim($data['email'] ?? '')),
        'phone'          => trim($data['phone'] ?? ''),
        'course'         => trim($data['course'] ?? ''),
        'year_level'     => (int)($data['year_level'] ?? 1),
        'status'         => trim($data['status'] ?? 'Active'),
    ];

    $st = $db->prepare($sql);
    $st->execute($fields);
    json(['success' => true, 'affected' => $st->rowCount()]);
}

function adminDelete(PDO $db): void {
    $data = readBody();
    if (empty($data['id'])) { jsonError('Missing id', 400); return; }

    $st = $db->prepare('DELETE FROM students WHERE id = ?');
    $st->execute([(int)$data['id']]);
    json(['success' => true, 'affected' => $st->rowCount()]);
}

function validateStudent(array $data, PDO $db, ?int $excludeId = null): array {
    $errors = [];
    $required = ['first_name', 'last_name', 'dob', 'gender', 'email', 'phone', 'student_number', 'course', 'year_level', 'status'];

    foreach ($required as $f) {
        if (empty(trim($data[$f] ?? ''))) {
            $errors[] = ucfirst(str_replace('_', ' ', $f)) . ' is required';
        }
    }
    if ($errors) return $errors;

    if (!preg_match("/^[A-Za-z\s'\-]{2,50}$/", $data['first_name']))
        $errors[] = 'First name is invalid';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'Email is invalid';

    $st = $db->prepare('SELECT id FROM students WHERE student_number = ? AND id != ?');
    $st->execute([trim($data['student_number']), $excludeId ?? 0]);
    if ($st->fetch()) $errors[] = 'Student number already exists';

    $st2 = $db->prepare('SELECT id FROM students WHERE email = ? AND id != ?');
    $st2->execute([strtolower(trim($data['email'])), $excludeId ?? 0]);
    if ($st2->fetch()) $errors[] = 'Email already exists';

    return $errors;
}

function readBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return $data ?? [];
}

function json(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $msg, int $code = 400): void {
    json(['success' => false, 'message' => $msg], $code);
}
