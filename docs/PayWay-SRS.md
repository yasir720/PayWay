# PayWay

## Software Requirements Specification (SRS)

Version: 1.1
Author: Yasir Alizai
Date: March 2026

---

# 1. Introduction

## 1.1 Purpose

This document describes the functional and non-functional requirements for **PayWay**, a web-based salary management system.

The current implementation enables authorized users to:

- View employee salary information
- Apply department-based raises
- Track salary history
- Enforce role-based access controls
- Log critical actions for audit purposes

---

## 1.2 Scope

PayWay is a browser-based application backed by a PHP API and a PostgreSQL database.

The system provides functionality for:

- Viewing current salaries and salary history
- Editing employee profile information (role-restricted)
- Applying automated, department-based salary raises
- Managing application users (Admin role)
- Auditing key actions via an audit log

PayWay is intended for employees, HR personnel, and system administrators.

---

## 1.3 Stakeholders

| Stakeholder           | Description                                                  |
| --------------------- | ------------------------------------------------------------ |
| Employees             | View their own salary information                            |
| HR Personnel          | Manage employee profiles, view salary data, and apply raises |
| System Administrators | Manage users, roles, and audit logs                          |

---

# 2. System Overview

PayWay is a centralized system designed to manage employee salary and compensation data. The system allows organizations to maintain accurate salary records, apply department-based salary increases, and view salary change history.

The system enforces role-based access controls to protect sensitive payroll information and ensure that only authorized users can access or modify salary and user records.

PayWay maintains an audit log of key actions to ensure transparency and accountability.

---

# 3. Functional Requirements

## 3.1 Employee Salary Tracking

The system shall allow authorized users to view employee salary information.

Requirements:

- The system shall store employee salary data in a centralized database.
- Employees shall be able to view their current salary.
- HR and Admin users shall be able to view current salaries for all active employees.
- HR and Admin users shall be able to view salary history records.
- HR users shall only see salary history for the past 2 years.

---

## 3.2 Salary Adjustment System

The system shall support applying department-based salary raises.

Requirements:

- Raises shall be calculated based on current salary rankings within each department.
- When raises are applied:
    - The top three employees in each department receive a **5%** raise
    - All other employees receive a **3%** raise
- New salary records shall be inserted with the current date as the effective date.
- A salary history entry shall be recorded for each raise application.

---

## 3.3 Employee Management

The system shall provide limited editing of employee profile information.

Requirements:

- HR and Admin users shall be able to update employee first name, last name, email, and department.
- HR users shall only be able to update employee records where the hire date is within the last 3 months.
- Employee records shall remain active unless updated outside the system.

---

## 3.4 Role-Based Access Control

The system shall enforce role-based permissions to protect sensitive information.

### Employee Role

Employees shall be able to:

- View their own salary information

Employees shall not be permitted to modify salary or employee data.

---

### HR Role

HR personnel shall be able to:

- View all employee salary information
- View salary history (past 2 years)
- Update employee profile information (limited to recent hires)
- Apply department-based raises

---

### Administrator Role

Administrators shall be able to:

- Perform all HR actions
- Create new user accounts
- View audit logs

---

# 4. Security Requirements

The system shall protect sensitive payroll information using authentication, authorization, and secure storage.

Requirements:

- The system shall require users to authenticate with a valid username and password.
- Passwords shall be stored as bcrypt hashes.
- The system shall enforce role-based authorization on all API endpoints.
- Sessions shall expire after 2 hours and idle out after 20 minutes.
- Sensitive actions (e.g., applying raises) shall require an authenticated session and role validation.

---

# 5. Logging and Auditing

PayWay shall maintain logs of critical system actions to support auditing and debugging.

The system shall log the following events:

- Salary changes
- Employee record updates
- User account modifications
- System errors

Each log entry shall include:

- Timestamp
- User performing the action
- Description of the change
- Affected system entity

---

# 6. Non-Functional Requirements

## 6.1 Performance

The system shall efficiently process salary queries, employee searches, and department-level salary comparisons.

---

## 6.2 Security

Sensitive payroll information shall be protected using authentication, authorization, and secure storage mechanisms.

---

## 6.3 Reliability

The system shall maintain accurate records of employee salary and compensation data.

---

## 6.4 Maintainability

The system should be designed in a modular manner to allow future enhancements and system updates.

---

# 7. Use Cases

## UC-1: View Salary Information

Actor: Employee

Description:
An employee views their current salary information.

Steps:

1. Employee logs into PayWay
2. System authenticates the user
3. Employee navigates to the dashboard
4. System displays the employee's current salary

---

## UC-2: View All Salaries and History

Actor: HR / Administrator

Description:
An HR or Admin user views current salaries for all employees and salary history.

Steps:

1. User logs into PayWay
2. User navigates to the dashboard
3. System displays current salaries for all active employees
4. System displays salary history (HR sees 2 years, Admin sees full history)

---

## UC-3: Update Employee Profile

Actor: HR / Administrator

Description:
An HR or Admin user updates an employee's profile information.

Steps:

1. User logs into PayWay
2. User selects an employee from the list
3. User updates fields (name, email, department)
4. System validates permissions and hire date restrictions
5. System saves the changes and logs the update

---

## UC-4: Apply Department Raises

Actor: HR / Administrator

Description:
An HR or Admin user applies department-based raises to all employees.

Steps:

1. User logs into PayWay
2. User triggers the "Apply Raises" action
3. System calculates raises based on department salary rankings
4. System inserts new salary records and logs history

---

## UC-5: Register a New User

Actor: Administrator

Description:
An administrator registers a new system user linked to an existing employee.

Steps:

1. Admin logs into PayWay
2. Admin navigates to the user registration section
3. Admin enters username, password, employee ID, and role
4. System validates input and creates the user account
5. System logs the new user creation

---

## UC-6: View Audit Logs

Actor: Administrator

Description:
An administrator views recent audit log entries.

Steps:

1. Admin logs into PayWay
2. Admin navigates to the audit log section
3. System displays recent audit events
