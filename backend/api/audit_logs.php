<?php
/**
 * API endpoint to fetch audit logs for admin dashboard.
 * Access is role-based (only admins can access).
 */

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];

if ($role != 3) {
    echo json_encode([
        'error' => 'Only admins can access this page',
    ]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.log_id,
           u.username,
           a.action_type,
           a.entity_modified,
           a.entity_id,
           a.description,
           a.timestamp
    FROM audit_logs a
    LEFT JOIN users u
           ON a.user_id = u.user_id
    ORDER BY a.timestamp DESC
    LIMIT 50
");

$stmt->execute();

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($logs);
