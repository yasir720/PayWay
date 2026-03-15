<?php
/**
 * API endpoint to register a new user.
 * Access is role-based.
 */

require_once './auth/auth.php';
require_once '../config/database.php';
require_once '../utils/validation.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

// Only Admins (role_id = 3) can register users
if ($role != 3) {
    http_response_code(403);
    echo json_encode([
        'message' => 'Unauthorized: Only Admins can register users',
    ]);
    exit();
}

// Parse request
$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$employee_id = $data['employee_id'] ?? '';
$role_id = $data['role_id'] ?? '';

// Validate required fields
if (!$username || !$password || !$employee_id || !$role_id) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing required fields']);
    exit();
}

// Validate username format
if (!validate_username($username)) {
    http_response_code(400);
    echo json_encode([
        'message' =>
            'Invalid username. Use 3-20 letters, numbers, underscores, or periods only.',
    ]);
    exit();
}

// Validate password strength
if (!validate_password($password)) {
    http_response_code(400);
    echo json_encode([
        'message' =>
            'Password is not safe. It must be at least 8 characters and include uppercase, lowercase, number, and special character.',
    ]);
    exit();
}

// Validate role_id
if (!in_array($role_id, [1, 2, 3])) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid role']);
    exit();
}

// Check employee exists and is active
$stmt = $pdo->prepare("
    SELECT employee_id, status
    FROM employees
    WHERE employee_id = :employee_id
");
$stmt->execute(['employee_id' => $employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    http_response_code(400);
    echo json_encode(['message' => 'Employee ID does not exist']);
    exit();
}

if ($employee['status'] !== 'active') {
    http_response_code(400);
    echo json_encode([
        'message' => 'Cannot create user for inactive employee',
    ]);
    exit();
}

// Ensure employee does not already have an account
$stmt = $pdo->prepare("
    SELECT user_id
    FROM users
    WHERE employee_id = :employee_id
");
$stmt->execute(['employee_id' => $employee_id]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['message' => 'This employee already has a user account']);
    exit();
}

// Ensure username is unique
$stmt = $pdo->prepare("
    SELECT user_id
    FROM users
    WHERE username = :username
");
$stmt->execute(['username' => $username]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['message' => 'Username already exists']);
    exit();
}

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert user + log audit in a transaction
try {
    $pdo->beginTransaction();

    // Insert user
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
    $new_user_id = $pdo->lastInsertId();

    // Log audit
    $log = $pdo->prepare("
        INSERT INTO audit_logs (user_id, action_type, entity_modified, entity_id, description)
        VALUES (:user_id, 'CREATE_USER', 'users', :entity_id, :description)
    ");
    $log->execute([
        'user_id' => $user_id,
        'entity_id' => $new_user_id,
        'description' => "User {$username} created with role {$role_id}",
    ]);

    $pdo->commit();

    echo json_encode(['message' => 'User registered successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'message' => 'User registration failed: ' . $e->getMessage(),
    ]);
}
?>
