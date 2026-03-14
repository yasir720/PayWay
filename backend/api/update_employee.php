<?php
/**
 * API endpoint to update employee information.
 * Access is role-based.
 */

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$role = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

$employee_id = $data['employee_id'];
$first_name = $data['first_name'];
$last_name = $data['last_name'];
$email = $data['email'];
$department_id = $data['department_id'];

if ($role == 1) {
    http_response_code(403);
    echo json_encode(['message' => 'Unauthorized access']);
    exit();
}

try {

    $pdo->beginTransaction();

    // get existing employee record
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, email, department_id, hire_date
        FROM employees
        WHERE employee_id = :id
    ");

    $stmt->execute(['id' => $employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        http_response_code(404);
        echo json_encode(['message' => 'Employee not found']);
        exit();
    }

    // HR restriction (3 months)
    if ($role == 2) {

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

    // update employee
    $update = $pdo->prepare("
        UPDATE employees
        SET first_name = :first,
            last_name = :last,
            email = :email,
            department_id = :dept
        WHERE employee_id = :id
    ");

    $update->execute([
        'first' => $first_name,
        'last' => $last_name,
        'email' => $email,
        'dept' => $department_id,
        'id' => $employee_id
    ]);

    // build change description
    $changes = [];

    if ($employee['first_name'] != $first_name) {
        $changes[] = "first_name: {$employee['first_name']} -> $first_name";
    }

    if ($employee['last_name'] != $last_name) {
        $changes[] = "last_name: {$employee['last_name']} -> $last_name";
    }

    if ($employee['email'] != $email) {
        $changes[] = "email: {$employee['email']} -> $email";
    }

    if ($employee['department_id'] != $department_id) {
        $changes[] = "department: {$employee['department_id']} -> $department_id";
    }

    if (!empty($changes)) {

        $description = implode(", ", $changes);

        $log = $pdo->prepare("
            INSERT INTO audit_logs
            (user_id, action_type, entity_modified, entity_id, description)
            VALUES (:user_id, 'UPDATE_EMPLOYEE', 'employees', :entity_id, :description)
        ");

        $log->execute([
            'user_id' => $user_id,
            'entity_id' => $employee_id,
            'description' => $description
        ]);
    }

    $pdo->commit();

    echo json_encode(['message' => 'Employee updated successfully']);

} catch (Exception $e) {

    $pdo->rollBack();

    http_response_code(500);

    echo json_encode([
        'message' => 'Update failed'
    ]);
}