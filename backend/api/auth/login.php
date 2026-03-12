<?php
/**
 * Simple login endpoint. Expects JSON {username,password}.
 * Verifies credentials and starts a session.
 * Login attempts are logged in the database for security monitoring.
 */

// configure and start a secure session
session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict',
]);
session_start();

header('Content-Type: application/json');

require_once '../../config/database.php';

// ensure database connection variable is available
if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode(['message' => 'internal server error']);
    exit();
}

// Helper function to log to audit_logs table
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

// parse JSON payload
$data = json_decode(file_get_contents('php://input'), true);

// reject malformed JSON explicitly
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['message' => 'Malformed JSON']);
    exit();
}

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// ensure both fields present
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['message' => 'username and password required']);
    exit();
}

// username format check
if (!preg_match('/^[a-zA-Z0-9_.]{3,20}$/', $username)) {
    http_response_code(400);
    echo json_encode([
        'message' =>
            'Invalid username. Use 3-20 letters, numbers, underscores, or periods only.',
    ]);
    exit();
}

// optional password strength check (disabled)
// if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
//     http_response_code(400);
//     echo json_encode(['message' => 'Password is not safe. It must be at least 8 characters and include uppercase, lowercase, number, and special character.']);
//     exit();
// }

// Query user by username
try {
    $stmt = $pdo->prepare("
    SELECT user_id, employee_id, username, password_hash, role_id
    FROM users
    WHERE username = :username
");

    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'internal server error']);
    exit();
}

// verify credentials
if (!$user || !password_verify($password, $user['password_hash'])) {
    // Log failed login (user_id may be null if username not found)

    $log_user_id = $user['user_id'] ?? null;

    log_audit(
        $pdo,
        $log_user_id,
        'LOGIN',
        'users',
        $log_user_id,
        "Failed login attempt for username '$username'",
    );
    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit();
}

// create fresh session state
session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['employee_id'] = $user['employee_id'];
$_SESSION['role_id'] = $user['role_id'];
$_SESSION['username'] = $user['username'];

$_SESSION['created'] = time();
$_SESSION['last_activity'] = time();

// log successful login
log_audit(
    $pdo,
    $user['user_id'],
    'LOGIN',
    'users',
    $user['user_id'],
    "User '$username' logged in successfully",
);

// send back minimal success info
echo json_encode([
    'message' => 'Login successful',
    'user_id' => $user['user_id'],
    'role_id' => $user['role_id'],
]);
