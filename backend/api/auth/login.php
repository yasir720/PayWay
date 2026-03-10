<?php
/**
 * Login API endpoint for user authentication.
 * Accepts JSON POST data with username and password, validates credentials,
 * and returns user ID on successful login.
 */

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

// Query user by username
$stmt = $pdo->prepare("
    SELECT user_id, username, password_hash
    FROM users
    WHERE username = :username
");

$stmt->execute(['username' => $username]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists
if (!$user) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit();
}

// Verify password against stored hash
if (!password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid credentials']);
    exit();
}

// Return success response with user ID
echo json_encode([
    'message' => 'Login successful',
    'user_id' => $user['user_id'],
]);
