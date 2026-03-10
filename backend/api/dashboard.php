<?php

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$employee_id = $_SESSION['employee_id'];

if ($role == 1) {
    // employee can only see themselves

    $stmt = $pdo->prepare("
        SELECT *
        FROM employees
        WHERE employee_id = :employee_id
    ");

    $stmt->execute(['employee_id' => $employee_id]);
} else {
    // HR and admin can see everyone

    $stmt = $pdo->prepare("
        SELECT *
        FROM employees
    ");

    $stmt->execute();
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
