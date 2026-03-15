/**
 * API endpoint to fetch current logged-in user's information.
 */

<?php
require_once './/auth.php'; // ensures session is active
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT user_id, username, role_id
        FROM users
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['message' => 'User not found']);
        exit();
    }

    echo json_encode($user); // returns {user_id, username, role_id}
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to fetch user']);
}


?>
