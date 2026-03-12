<?php
/**
 * Simple login endpoint. Expects JSON {username,password},
 * verifies credentials and starts a session.
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

// parse JSON payload
$data = json_decode(file_get_contents('php://input'), true);

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
$stmt = $pdo->prepare("
    SELECT user_id, employee_id, username, password_hash, role_id
    FROM users
    WHERE username = :username
");

$stmt->execute(['username' => $username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// verify credentials
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit();
}

// create fresh session state
session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['employee_id'] = $user['employee_id'];
$_SESSION['role_id'] = $user['role_id'];

$_SESSION['created'] = time();
$_SESSION['last_activity'] = time();

// send back minimal success info
echo json_encode([
    'message' => 'Login successful',
    'user_id' => $user['user_id'],
    'role_id' => $user['role_id'],
]);
