<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
try {
    $db = getDB();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'tables' => $tables]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
