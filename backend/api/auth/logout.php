<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_destroy();

// Send JSON response
header('Content-Type: application/json');
echo json_encode([
    'message' => 'Logged out successfully',
]);
