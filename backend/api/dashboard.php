<?php
/**
 * API endpoint to fetch employee data for dashboard.
 * Access is role-based.
 */

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$employee_id = $_SESSION['employee_id'];

if ($role == 1) {
    $stmt = $pdo->prepare("
        SELECT e.employee_id,
               e.first_name,
               e.last_name,
               e.email,
               d.department_name
               d.department_id
        FROM employees e
        LEFT JOIN departments d 
               ON e.department_id = d.department_id
        WHERE e.employee_id = :employee_id
          AND e.status = 'active'
          ORDER BY d.department_id, e.last_name, e.first_name
    ");

    $stmt->execute(['employee_id' => $employee_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT e.employee_id,
               e.first_name,
               e.last_name,
               e.email,
               d.department_name,
               d.department_id
        FROM employees e
        LEFT JOIN departments d 
               ON e.department_id = d.department_id
        WHERE e.status = 'active'
        ORDER BY d.department_id, e.last_name, e.first_name
    ");

    $stmt->execute();
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
