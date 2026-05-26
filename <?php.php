<?php
/**
 * StudentBase — api.php
 * RESTful PHP backend for student CRUD operations.
 *
 * Endpoints (query param ?action=):
 *   GET  ?action=list           → JSON array of all students
 *   POST ?action=create         → Insert new student, returns {success, id}
 *   POST ?action=update         → Update existing student, returns {success}
 *   POST ?action=delete         → Soft-delete or hard-delete, returns {success}
 *
 * Configuration: update DB_* constants below to match your environment.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

/* ─── DB CONFIG ─────────────────────────────────────────────────────── */
define('DB_HOST', 'localhost');
define('DB_NAME', 'studentbase');
define('DB_USER', 'root');        // change in production
define('DB_PASS', '');            // change in production
define('DB_CHAR', 'utf8mb4');

/* ─── CONNECTION ─────────────────────────────────────────────────────── */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/* ─── ROUTER ─────────────────────────────────────────────────────────── */
$action = $_GET['action'] ?? '';

try {
    $db = getDB();
    match ($action) {
        'list'   => handleList($db),
        'create' => handleCreate($db),
        'update' => handleUpdate($db),
        'delete' => handleDelete($db),
        default  => jsonError('Unknown action', 400),
    };
} catch (PDOException $e) {
    jsonError('Database error: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}

/* ─── HANDLERS ───────────────────────────────────────────────────────── */

/**
 * GET ?action=list[&search=...][&status=...][&page=1][&per_page=50]
 */
function handleList(PDO $db): void {
    $search   = trim($_GET['search']   ?? '');
    $status   = trim($_GET['status']   ?? '');
    $page     = max(1, (int)($_GET['page']     ?? 1));
    $perPage  = min(100, max(1, (int)($_GET['per_page'] ?? 50)));
    $offset   = ($page - 1) * $perPage;

    $where  = [];
    $params = [];

    if ($search !== '') {
        $like = "%{$search}%";
        $where[]   = '(CONCAT(first_name," ",last_name) LIKE ? OR student_number LIKE ? OR email LIKE ? OR course LIKE ?)';
        $params = array_merge($params, [$like, $like, $like, $like]);
    }
    if ($status !== '') {
        $where[]   = 'status = ?';
        $params[]  = $status;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $countSql = "SELECT COUNT(*) FROM students $whereSql";
    $dataSql  = "SELECT * FROM students $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";

    $total   = (int) $db->prepare($countSql)->execute($params) ? $db->prepare($countSql)->execute($params) : 0;
    $stTotal = $db->prepare($countSql);
    $stTotal->execute($params);
    $total = (int) $stTotal->fetchColumn();

    $st = $db->prepare($dataSql);
    $st->execute($params);
    $rows = $st->fetchAll();

    json(['success' => true, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'data' => $rows]);
}

/**
 * POST ?action=create  body: JSON student object
 */
function handleCreate(PDO $db): void {
    $data = readBody();
    $errs = validateStudent($data, $db);
    if ($errs) { jsonError(implode('; ', $errs), 422); return; }

    $sql = "INSERT INTO students
              (student_number, first_name, last_name, dob, gender, address,
               email, phone, emergency_contact, emergency_phone,
               course, year_level, intake, gpa, status)
            VALUES
              (:student_number,:first_name,:last_name,:dob,:gender,:address,
               :email,:phone,:emergency_contact,:emergency_phone,
               :course,:year_level,:intake,:gpa,:status)";

    $st = $db->prepare($sql);
    $st->execute(sanitizeFields($data));
    json(['success' => true, 'id' => (int) $db->lastInsertId()]);
}

/**
 * POST ?action=update  body: JSON student object (must include id)
 */
function handleUpdate(PDO $db): void {
    $data = readBody();
    if (empty($data['id'])) { jsonError('Missing id', 400); return; }

    $errs = validateStudent($data, $db, (int)$data['id']);
    if ($errs) { jsonError(implode('; ', $errs), 422); return; }

    $sql = "UPDATE students SET
               student_number    = :student_number,
               first_name        = :first_name,
               last_name         = :last_name,
               dob               = :dob,
               gender            = :gender,
               address           = :address,
               email             = :email,
               phone             = :phone,
               emergency_contact = :emergency_contact,
               emergency_phone   = :emergency_phone,
               course            = :course,
               year_level        = :year_level,
               intake            = :intake,
               gpa               = :gpa,
               status            = :status,
               updated_at        = CURRENT_TIMESTAMP
            WHERE id = :id";

    $fields       = sanitizeFields($data);
    $fields['id'] = (int)$data['id'];

    $st = $db->prepare($sql);
    $st->execute($fields);
    json(['success' => true, 'affected' => $st->rowCount()]);
}

/**
 * POST ?action=delete  body: {id: N}
 */
function handleDelete(PDO $db): void {
    $data = readBody();
    if (empty($data['id'])) { jsonError('Missing id', 400); return; }

    $st = $db->prepare('DELETE FROM students WHERE id = ?');
    $st->execute([(int)$data['id']]);
    json(['success' => true, 'affected' => $st->rowCount()]);
}

/* ─── VALIDATION ─────────────────────────────────────────────────────── */
function validateStudent(array $data, PDO $db, ?int $excludeId = null): array {
    $errors = [];

    // Required fields
    $required = ['first_name','last_name','dob','gender','email','phone','student_number','course','year_level','status'];
    foreach ($required as $f) {
        if (empty(trim($data[$f] ?? ''))) {
            $errors[] = ucfirst(str_replace('_',' ',$f)) . ' is required';
        }
    }
    if ($errors) return $errors; // stop early

    // Name format
    if (!preg_match("/^[A-Za-z\s'\-]{2,50}$/", $data['first_name']))
        $errors[] = 'First name is invalid';
    if (!preg_match("/^[A-Za-z\s'\-]{2,50}$/", $data['last_name']))
        $errors[] = 'Last name is invalid';

    // Email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'Email address is invalid';

    // DOB
    $dob = strtotime($data['dob']);
    if (!$dob || $dob > time())
        $errors[] = 'Date of birth is invalid or in the future';
    elseif ((time() - $dob) < 13 * 365.25 * 86400)
        $errors[] = 'Student must be at least 13 years old';

    // GPA
    if (!empty($data['gpa'])) {
        $gpa = (float)$data['gpa'];
        if ($gpa < 0 || $gpa > 4)
            $errors[] = 'GPA must be between 0.00 and 4.00';
    }

    // Year level
    if (!in_array((int)($data['year_level'] ?? 0), [1,2,3,4,5]))
        $errors[] = 'Year level must be 1–5';

    // Status
    $validStatuses = ['Active','Inactive','Graduated','Suspended','Deferred'];
    if (!in_array($data['status'] ?? '', $validStatuses))
        $errors[] = 'Invalid status value';

    // Unique student_number
    $st = $db->prepare('SELECT id FROM students WHERE student_number = ?');
    $st->execute([trim($data['student_number'])]);
    $existing = $st->fetch();
    if ($existing && (int)$existing['id'] !== (int)$excludeId)
        $errors[] = 'Student number already exists';

    // Unique email
    $st2 = $db->prepare('SELECT id FROM students WHERE email = ?');
    $st2->execute([trim($data['email'])]);
    $existingEmail = $st2->fetch();
    if ($existingEmail && (int)$existingEmail['id'] !== (int)$excludeId)
        $errors[] = 'Email address already registered';

    return $errors;
}

/* ─── HELPERS ────────────────────────────────────────────────────────── */
function sanitizeFields(array $d): array {
    return [
        'student_number'    => trim($d['student_number']    ?? ''),
        'first_name'        => trim($d['first_name']        ?? ''),
        'last_name'         => trim($d['last_name']         ?? ''),
        'dob'               => $d['dob']                    ?? null,
        'gender'            => trim($d['gender']            ?? ''),
        'address'           => trim($d['address']           ?? ''),
        'email'             => strtolower(trim($d['email']  ?? '')),
        'phone'             => trim($d['phone']             ?? ''),
        'emergency_contact' => trim($d['emergency_contact'] ?? ''),
        'emergency_phone'   => trim($d['emergency_phone']   ?? ''),
        'course'            => trim($d['course']            ?? ''),
        'year_level'        => (int)($d['year_level']       ?? 1),
        'intake'            => trim($d['intake']            ?? ''),
        'gpa'               => isset($d['gpa']) && $d['gpa'] !== '' ? (float)$d['gpa'] : null,
        'status'            => trim($d['status']            ?? 'Active'),
    ];
}

function readBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE)
        jsonError('Invalid JSON body', 400);
    return $data ?? [];
}

function json(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function jsonError(string $msg, int $code = 400): void {
    json(['success' => false, 'message' => $msg], $code);
}
