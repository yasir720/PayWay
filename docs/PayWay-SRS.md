# PayWay

## Software Requirements Specification (SRS)

Version: 1.0
 Author: Yasir Alizai
 Date: March 2026

------

# 1. Introduction

## 1.1 Purpose

This document describes the functional and non-functional requirements for **PayWay**, a salary management system designed to track employee compensation, manage salary adjustments, and support employee lifecycle management within an organization.

The system will allow authorized users to manage salary records, monitor employee compensation history, and apply salary adjustments based on defined performance metrics. PayWay will enforce role-based access control and maintain audit logs for critical system actions.

------

## 1.2 Scope

PayWay is a web-based application that enables organizations to manage employee salary data and related records securely.

The system will provide functionality for:

- Tracking employee salary information
- Managing salary adjustments and raises
- Recording employee hiring and termination
- Managing paid time off (PTO) and payroll deductions
- Enforcing role-based system permissions
- Logging system actions for auditing and debugging purposes

PayWay will be used primarily by employees, HR personnel, and system administrators.

------

## 1.3 Stakeholders

| Stakeholder           | Description                                                  |
| --------------------- | ------------------------------------------------------------ |
| Employees             | View their personal salary and PTO information               |
| HR Personnel          | Manage employee records, salary adjustments, hiring, and termination |
| System Administrators | Manage user accounts, permissions, and system configuration  |

------

# 2. System Overview

PayWay is a centralized system designed to manage employee salary and compensation data. The system will allow organizations to maintain accurate records of employee salaries while supporting automated raise calculations based on performance metrics.

The system will enforce strict security controls to protect sensitive payroll information and ensure that only authorized users can access or modify salary records.

PayWay will also maintain historical records of salary changes to ensure transparency and accountability.

------

# 3. Functional Requirements

## 3.1 Employee Salary Tracking

The system shall allow authorized users to track employee salary information.

Requirements:

- The system shall store employee salary data in a centralized database.
- The system shall allow users to view salary records.
- The system shall allow salary information to be sorted and filtered by:
  - Department
  - Hire date
  - Salary amount (highest to lowest)
  - Salary amount (lowest to highest)
- The system shall allow employees to view their own salary and salary history.

------

## 3.2 Salary Adjustment System

The system shall support salary adjustments based on performance metrics.

Requirements:

- The system shall allow salary raises to be calculated using employee performance metrics.
- The system shall support department-based salary comparisons.
- The system shall allow HR personnel to apply raises such that:
  - The top three employees in each department receive a **5% salary increase**
  - All remaining employees receive a **3% salary increase**
- The system shall store a historical record of all salary adjustments.

------

## 3.3 Employee Lifecycle Management

The system shall support the management of employee hiring and termination.

Requirements:

- The system shall allow HR personnel to add new employee records.
- The system shall allow HR personnel to terminate employee records.
- The system shall track:
  - Employee hire dates
  - Employee termination dates
- The system shall allow HR personnel to manage payroll-related records including:
  - Paid time off (PTO)
  - Payroll deductions

------

## 3.4 Role-Based Access Control

The system shall enforce role-based permissions to protect sensitive information.

### Employee Role

Employees shall be able to:

- View their own salary information
- View salary history
- View PTO balances

Employees shall not be permitted to modify salary or employee data.

------

### HR Role

HR personnel shall be able to:

- View employee salary information
- Modify salary records within the previous **three months**
- Add new employee records
- Terminate employee records
- Manage PTO and deduction records

HR personnel shall not be permitted to modify salary records older than three months.

------

### Administrator Role

Administrators shall be able to:

- Create and manage user accounts
- Assign and modify system roles
- Modify salary records beyond the HR modification window
- Manage system configuration settings

------

# 4. Security Requirements

The system shall protect sensitive payroll information through authentication and authorization controls.

Requirements:

- The system shall require users to authenticate using valid credentials.
- The system shall enforce role-based authorization for system actions.
- The system shall require confirmation prompts before major salary changes are finalized.
- The system shall generate alerts when unusually large salary changes occur.

------

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

------

# 6. Non-Functional Requirements

## 6.1 Performance

The system shall efficiently process salary queries, employee searches, and department-level salary comparisons.

------

## 6.2 Security

Sensitive payroll information shall be protected using authentication, authorization, and secure storage mechanisms.

------

## 6.3 Reliability

The system shall maintain accurate records of employee salary and compensation data.

------

## 6.4 Maintainability

The system should be designed in a modular manner to allow future enhancements and system updates.

------

# 7. Use Cases

## UC-1: View Salary Information

Actor: Employee

Description:
 An employee views their salary information and salary history.

Steps:

1. Employee logs into PayWay
2. System authenticates the user
3. Employee navigates to the salary dashboard
4. System displays salary data

------

## UC-2: View PTO Balance

Actor: Employee

Steps:

1. Employee logs into PayWay
2. Employee navigates to the PTO page
3. System retrieves PTO records
4. System displays available PTO

------

## UC-3: Add New Employee

Actor: HR

Steps:

1. HR logs into PayWay
2. HR selects "Add Employee"
3. HR enters employee information
4. System validates the information
5. System creates a new employee record

------

## UC-4: Update Employee Salary

Actor: HR

Steps:

1. HR searches for an employee
2. HR enters updated salary information
3. System verifies modification is within the allowed timeframe
4. System requests confirmation
5. System saves the salary update

------

## UC-5: Apply Performance Raises

Actor: HR

Steps:

1. HR selects a department
2. System ranks employees by performance score
3. System identifies the top three employees
4. System applies raises:
   - 5% increase to top three employees
   - 3% increase to remaining employees
5. System records salary adjustment history

------

## UC-6: Manage User Accounts

Actor: Administrator

Steps:

1. Administrator logs into PayWay
2. Administrator views system users
3. Administrator creates, modifies, or deletes accounts
4. System logs account changes

------

# 8. User Stories

### Employee

**Story 1**

As an employee
 I want to view my salary history
 So that I can track changes to my compensation.

------

**Story 2**

As an employee
 I want to see my PTO balance
 So that I know how many days off I can take.

------

### HR Personnel

**Story 3**

As an HR user
 I want to add new employees
 So that their information is stored in PayWay.

------

**Story 4**

As an HR user
 I want to update employee salaries within a limited timeframe
 So that payroll errors can be corrected.

------

**Story 5**

As an HR user
 I want to apply raises automatically
 So that salary increases are applied consistently across departments.

------

### Administrator

**Story 6**

As an administrator
 I want to manage user roles
 So that the correct users have appropriate system permissions.