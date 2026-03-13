<?php
/**
 * API endpoint to log out the current user.
 * It destroys the session and logs the logout action in the audit logs.
 */

session_start();

require_once '../../config/database.php';

// helper function to log audit entries
function log_audit(
    PDO $pdo,
    $user_id,
    $action_type,
    $entity_modified = null,
    $entity_id = null,
    $description = null,
) {
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, action_type, entity_modified, entity_id, description)
        VALUES (:user_id, :action_type, :entity_modified, :entity_id, :description)
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':action_type' => $action_type,
        ':entity_modified' => $entity_modified,
        ':entity_id' => $entity_id,
        ':description' => $description,
    ]);
}

// capture user ID from session if available
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;

// log the logout action if we know the user
if ($user_id) {
    log_audit(
        $pdo,
        $user_id,
        'LOGOUT',
        'users',
        $user_id,
        "User '$username' logged out successfully",
    );
}

// destroy all session data
$_SESSION = [];
session_destroy();

// send JSON response
header('Content-Type: application/json');
echo json_encode([
    'message' => 'Logged out successfully',
]);
