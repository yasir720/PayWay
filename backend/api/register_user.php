<?php
/**
 * API endpoint to register a new user.
 * Access is role-based.
 */

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

if ($role != 3) {
    http_response_code(403);
    echo json_encode([
        'message' => 'Unauthorized: Only Admins can register users',
    ]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username']);
$password = $data['password'];
$employee_id = $data['employee_id'];
$role_id = $data['role_id'];

// Validate inputs
if (!$username || !$password || !$employee_id || !$role_id) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing required fields']);
    exit();
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password_hash, employee_id, role_id)
        VALUES (:username, :password_hash, :employee_id, :role_id)
    ");

    $stmt->execute([
        'username' => $username,
        'password_hash' => $password_hash,
        'employee_id' => $employee_id,
        'role_id' => $role_id,
    ]);

    // Log creation
    $log = $pdo->prepare("
        INSERT INTO audit_logs (user_id, action_type, entity_modified, entity_id, description)
        VALUES (:user_id, 'CREATE_USER', 'users', :entity_id, :description)
    ");

    $log->execute([
        'user_id' => $user_id,
        'entity_id' => $pdo->lastInsertId(),
        'description' => "User {$username} created with role {$role_id}",
    ]);

    echo json_encode(['message' => 'User registered successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'User registration failed: ' . $e->getMessage(),
    ]);
}
?>
