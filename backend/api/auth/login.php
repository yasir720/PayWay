<?php
/**
 * Login API endpoint for user authentication.
 * Accepts JSON POST data with username and password, validates credentials,
 * and returns user ID on successful login.
 */

session_start();

header('Content-Type: application/json');

require_once '../../config/database.php';

// Decode JSON input from request body
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// Validate required fields
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['message' => 'username and password required']);
    exit();
}

// Validate username (letters, numbers, underscores, 3-20 chars)
if (!preg_match('/^[a-zA-Z0-9_.]{3,20}$/', $username)) {
    http_response_code(400);
    echo json_encode([
        'message' =>
            'Invalid username. Use 3-20 letters, numbers, underscores, or periods only.',
    ]);
    exit();
}

// Validate password (min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char)
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

// Check if user exists and verify password against stored hash
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit();
}

// Store secure session data
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['employee_id'] = $user['employee_id'];
$_SESSION['role_id'] = $user['role_id'];

// Return success response with user ID
echo json_encode([
    'message' => 'Login successful',
    'user_id' => $user['user_id'],
    'role_id' => $user['role_id'],
]);
