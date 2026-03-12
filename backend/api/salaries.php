<?php

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$employee_id = $_SESSION['employee_id'];

if ($role == 1) {
    // Employee sees only current salary
    $stmt = $pdo->prepare("
        SELECT TO_CHAR(s.salary_amount, 'FM999,999,999,990.00') AS salary_amount,
               s.effective_date,
               e.first_name, e.last_name
        FROM salaries s
        JOIN employees e ON s.employee_id = e.employee_id
        WHERE e.employee_id = :id
          AND s.effective_date = (
              SELECT MAX(s2.effective_date)
              FROM salaries s2
              WHERE s2.employee_id = s.employee_id
          )
    ");
    $stmt->execute(['id' => $employee_id]);

    $result = [
        'current' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'history' => [], // no history for employee
    ];
} else {
    // HR and Admin
    $stmtCurrent = $pdo->query("
        SELECT TO_CHAR(s.salary_amount, 'FM999,999,999,990.00') AS salary_amount,
               s.effective_date,
               e.first_name, e.last_name
        FROM salaries s
        JOIN employees e ON s.employee_id = e.employee_id
        WHERE s.effective_date = (
            SELECT MAX(s2.effective_date)
            FROM salaries s2
            WHERE s2.employee_id = s.employee_id
        )
        ORDER BY e.last_name, e.first_name
    ");

    $current = $stmtCurrent->fetchAll(PDO::FETCH_ASSOC);

    // Salary history
    if ($role == 2) {
        // HR: past 2 years only
        $stmtHistory = $pdo->prepare("
            SELECT h.employee_id,
                   e.first_name,
                   e.last_name,
                   TO_CHAR(h.old_salary, 'FM999,999,999,990.00') AS old_salary,
                   TO_CHAR(h.new_salary, 'FM999,999,999,990.00') AS new_salary,
                   TO_CHAR(h.change_date, 'YYYY-MM-DD') AS change_date,
                   h.change_reason
            FROM salary_history h
            JOIN employees e ON h.employee_id = e.employee_id
            WHERE h.change_date >= (CURRENT_DATE - INTERVAL '2 years')
            ORDER BY h.change_date DESC
        ");
        $stmtHistory->execute();
    } else {
        // Admin: all history
        $stmtHistory = $pdo->prepare("
            SELECT h.employee_id,
                   e.first_name,
                   e.last_name,
                   TO_CHAR(h.old_salary, 'FM999,999,999,990.00') AS old_salary,
                   TO_CHAR(h.new_salary, 'FM999,999,999,990.00') AS new_salary,
                   TO_CHAR(h.change_date, 'YYYY-MM-DD') AS change_date,
                   h.change_reason,
                   h.change_percent
            FROM salary_history h
            JOIN employees e ON h.employee_id = e.employee_id
            ORDER BY h.change_date DESC
        ");
        $stmtHistory->execute();
    }

    $result = [
        'current' => $current,
        'history' => $stmtHistory->fetchAll(PDO::FETCH_ASSOC),
    ];
}

echo json_encode($result);
