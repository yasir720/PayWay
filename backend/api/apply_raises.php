<?php

require_once './auth/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$role = $_SESSION['role_id'];
$user_id = $_SESSION['user_id'];

if ($role == 1) {
    http_response_code(403);
    echo json_encode(["message" => "Employees cannot apply raises"]);
    exit();
}

try {

    $pdo->beginTransaction();

    /*
    Get latest salaries and determine raise amounts
    */

    $query = "

    WITH current_salaries AS (

        SELECT
            e.employee_id,
            e.department_id,
            s.salary_amount AS old_salary

        FROM employees e

        JOIN salaries s
            ON e.employee_id = s.employee_id

        WHERE s.effective_date = (
            SELECT MAX(s2.effective_date)
            FROM salaries s2
            WHERE s2.employee_id = e.employee_id
        )

    ),

    ranked AS (

        SELECT
            employee_id,
            department_id,
            old_salary,

            RANK() OVER (
                PARTITION BY department_id
                ORDER BY old_salary DESC
            ) AS salary_rank

        FROM current_salaries
    ),

    raises AS (

        SELECT
            employee_id,
            old_salary,

            CASE
                WHEN salary_rank <= 3
                    THEN old_salary * 1.05
                ELSE old_salary * 1.03
            END AS new_salary,

            CASE
                WHEN salary_rank <= 3
                    THEN 5.00
                ELSE 3.00
            END AS change_percent

        FROM ranked
    )

    INSERT INTO salaries (employee_id, salary_amount, effective_date)

    SELECT
        employee_id,
        new_salary,
        CURRENT_DATE

    FROM raises;

    ";

    $pdo->exec($query);

    /*
    Insert salary history records
    */

    $historyQuery = "

    WITH current_salaries AS (

        SELECT
            e.employee_id,
            e.department_id,
            s.salary_amount AS old_salary

        FROM employees e

        JOIN salaries s
            ON e.employee_id = s.employee_id

        WHERE s.effective_date = (
            SELECT MAX(s2.effective_date)
            FROM salaries s2
            WHERE s2.employee_id = e.employee_id
        )

    ),

    ranked AS (

        SELECT
            employee_id,
            department_id,
            old_salary,

            RANK() OVER (
                PARTITION BY department_id
                ORDER BY old_salary DESC
            ) AS salary_rank

        FROM current_salaries
    ),

    raises AS (

        SELECT
            employee_id,
            old_salary,

            CASE
                WHEN salary_rank <= 3
                    THEN old_salary * 1.05
                ELSE old_salary * 1.03
            END AS new_salary,

            CASE
                WHEN salary_rank <= 3
                    THEN 5.00
                ELSE 3.00
            END AS change_percent

        FROM ranked
    )

    INSERT INTO salary_history
        (employee_id, old_salary, new_salary, change_percent, change_reason, changed_by)

    SELECT
        employee_id,
        old_salary,
        new_salary,
        change_percent,
        'Department performance raise',
        :user_id

    FROM raises;

    ";

    $stmt = $pdo->prepare($historyQuery);
    $stmt->execute(['user_id' => $user_id]);

    $pdo->commit();

    echo json_encode([
        "message" => "Raises applied successfully and logged in salary_history"
    ]);

} catch (Exception $e) {

    $pdo->rollBack();

    http_response_code(500);

    echo json_encode([
        "message" => "Raise process failed"
    ]);
}