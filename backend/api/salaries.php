<?php

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$employee_id = $_SESSION['employee_id'];

if ($role == 1) {
    $stmt = $pdo->prepare("
        SELECT s.salary_amount, s.effective_date,
               e.first_name, e.last_name
        FROM salaries s
        JOIN employees e ON s.employee_id = e.employee_id
        WHERE e.employee_id = :id
        ORDER BY effective_date DESC
    ");

    $stmt->execute(['id' => $employee_id]);
} else {
    $stmt = $pdo->query("
        SELECT s.salary_amount, s.effective_date,
               e.first_name, e.last_name
        FROM salaries s
        JOIN employees e ON s.employee_id = e.employee_id
        ORDER BY effective_date DESC
    ");
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
