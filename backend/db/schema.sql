-- Schema file for PayWay backend (PostgreSQL)
-- This file is intended as a reference to see the database structure.

CREATE TABLE departments (
    department_id SERIAL PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE employees (
    employee_id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    hire_date DATE NOT NULL,
    termination_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'active',

    CONSTRAINT fk_employee_department
        FOREIGN KEY (department_id)
        REFERENCES departments(department_id)
        ON DELETE RESTRICT
);

CREATE TABLE salaries (
    salary_id SERIAL PRIMARY KEY,
    employee_id INT NOT NULL,
    salary_amount NUMERIC(12,2) NOT NULL CHECK (salary_amount > 0),
    effective_date DATE NOT NULL,

    CONSTRAINT fk_salary_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
        ON DELETE CASCADE
);

CREATE TABLE salary_history (
    history_id SERIAL PRIMARY KEY,
    employee_id INT NOT NULL,
    old_salary NUMERIC(12,2) NOT NULL,
    new_salary NUMERIC(12,2) NOT NULL,
    change_percent NUMERIC(5,2),
    change_reason VARCHAR(255),
    changed_by INT,
    change_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_history_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
        ON DELETE CASCADE
);

CREATE TABLE performance_metrics (
    metric_id SERIAL PRIMARY KEY,
    employee_id INT NOT NULL,
    review_period VARCHAR(50) NOT NULL,
    performance_score NUMERIC(5,2) NOT NULL CHECK (performance_score >= 0),

    CONSTRAINT fk_performance_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
        ON DELETE CASCADE
);

CREATE TABLE pto_records (
    pto_id SERIAL PRIMARY KEY,
    employee_id INT NOT NULL,
    pto_days_available INT NOT NULL DEFAULT 0 CHECK (pto_days_available >= 0),
    pto_days_used INT NOT NULL DEFAULT 0 CHECK (pto_days_used >= 0),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pto_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
        ON DELETE CASCADE
);

CREATE TABLE deductions (
    deduction_id SERIAL PRIMARY KEY,
    employee_id INT NOT NULL,
    deduction_type VARCHAR(100) NOT NULL,
    amount NUMERIC(10,2) NOT NULL CHECK (amount >= 0),
    effective_date DATE NOT NULL,

    CONSTRAINT fk_deduction_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
        ON DELETE CASCADE
);

CREATE TABLE roles (
    role_id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    employee_id INT UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_employee
        FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id)
        ON DELETE SET NULL,

    CONSTRAINT fk_user_role
        FOREIGN KEY (role_id)
        REFERENCES roles(role_id)
        ON DELETE RESTRICT
);

CREATE TABLE audit_logs (
    log_id SERIAL PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(100) NOT NULL,
    entity_modified VARCHAR(100),
    entity_id INT,
    description TEXT,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL
);


CREATE INDEX idx_employee_department
ON employees(department_id);

CREATE INDEX idx_salary_employee
ON salaries(employee_id);

CREATE INDEX idx_performance_employee
ON performance_metrics(employee_id);

CREATE INDEX idx_audit_user
ON audit_logs(user_id);
