<?php

require_once './../auth/auth.php';
require_once './../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$role = $_SESSION['role_id'];

$employee_id = $data['employee_id'];
$email = $data['email'];

if ($role == 1) {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized access']);
    exit();
}

if ($role == 2) {
    // HR limited to last 3 months

    $stmt = $pdo->prepare("
        SELECT hire_date
        FROM employees
        WHERE employee_id = :id
    ");

    $stmt->execute(['id' => $employee_id]);

    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        http_response_code(404);
        exit();
    }

    $hire_date = strtotime($employee['hire_date']);
    $three_months = strtotime('-3 months');

    if ($hire_date < $three_months) {
        http_response_code(403);
        echo json_encode([
            'message' => 'HR cannot modify records older than 3 months',
        ]);
        exit();
    }
}

$stmt = $pdo->prepare("
    UPDATE employees
    SET email = :email
    WHERE employee_id = :id
");

$stmt->execute([
    'email' => $email,
    'id' => $employee_id,
]);

echo json_encode(['message' => 'Employee updated']);
