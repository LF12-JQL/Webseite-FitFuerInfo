<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

// Session check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isSystemverwalter()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    global $pdo;
    $count = $pdo->query("SELECT COUNT(*) FROM users WHERE password_change_status = 'requested'")->fetchColumn();
    echo json_encode(['count' => (int)$count]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
