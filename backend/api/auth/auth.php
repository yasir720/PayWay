<?php

session_start();

$maxSessionTime = 2 * 60 * 60; // 2 hours
$maxIdleTime = 20 * 60; // 20 minutes

// User not logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'message' => 'Unauthorized',
    ]);
    exit();
}

// Check absolute session lifetime
if (
    isset($_SESSION['created']) &&
    time() - $_SESSION['created'] > $maxSessionTime
) {
    session_unset();
    session_destroy();

    http_response_code(401);
    echo json_encode(['message' => 'Session expired']);
    exit();
}

// Check inactivity timeout
if (
    isset($_SESSION['last_activity']) &&
    time() - $_SESSION['last_activity'] > $maxIdleTime
) {
    session_unset();
    session_destroy();

    http_response_code(401);
    echo json_encode(['message' => 'Session timed out due to inactivity']);
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
