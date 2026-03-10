# PayWay

## System Design Document (SDD)

Version: 1.0  
Author: Yasir Alizai  
Date: March 2026

---

# 1. Introduction

## 1.1 Purpose

This document describes the technical design and architecture of **PayWay**, a salary management system used to manage employee salary data, track compensation history, and administer employee records securely.

The document outlines the technologies used, system architecture, data design, and API structure that will support the implementation of the system.

---

# 1.2 Technology Stack

PayWay will use the following technologies:

Technology

Purpose

PostgreSQL

Relational database for storing application data

AWS RDS

Managed hosting for the PostgreSQL database

PHP

Backend application logic

HTML

Structure of web pages

CSS

User interface styling

Style Guides

Ensuring consistent UI design

JSON

Data format used for API communication

HTTP/HTTPS

Communication protocol between client and server

OAuth

Secure authentication and authorization

Object-Oriented Programming (OOP)

Application design methodology

Jira

Project management and issue tracking

---

# 2. System Architecture

PayWay will follow a **three-tier architecture** consisting of a presentation layer, application layer, and data layer.

Client (Browser)  
 |  
 | HTTPS  
 |  
Web Server (PHP Application)  
 |  
 | SQL Queries  
 |  
AWS RDS PostgreSQL Database

### Components

**Client Layer**

- HTML pages rendered in the browser
- CSS styling for UI
- Forms for user interaction

**Application Layer**

- PHP backend services
- Business logic for salary calculations
- Authentication and authorization
- API endpoints returning JSON responses

**Data Layer**

- PostgreSQL database hosted on AWS RDS
- Stores employee records, salary history, and system logs

---

# 3. Application Architecture

The backend will follow an **Object-Oriented architecture** separating application responsibilities into distinct components.

### Main Application Layers

Controllers  
Business Logic / Services  
Data Access Layer  
Database

---

## 3.1 Controllers

Controllers handle incoming HTTP requests and determine the appropriate system response.

Example responsibilities:

- Receiving user input
- Validating request data
- Calling business logic services
- Returning JSON responses

Example:

EmployeeController  
SalaryController  
AuthController  
AdminController

---

## 3.2 Services (Business Logic Layer)

Services contain the core application logic.

Responsibilities include:

- Calculating raises
- Validating permissions
- Managing employee lifecycle operations
- Processing salary updates

Example:

SalaryService  
EmployeeService  
AuthService  
AuditService

---

## 3.3 Data Access Layer

The data access layer interacts directly with the PostgreSQL database.

Responsibilities include:

- Executing SQL queries
- Returning structured data to services
- Managing database transactions

Example:

EmployeeRepository  
SalaryRepository  
UserRepository  
AuditRepository

---

# 4. Authentication and Authorization

PayWay will use **OAuth-based authentication** to secure access to the system.

### Authentication Flow

User logs in  
 |  
OAuth provider authenticates user  
 |  
Access token returned  
 |  
User accesses PayWay APIs

### Security Controls

- OAuth access tokens required for API access
- Role-based access control
- Passwords stored using hashing
- HTTPS enforced for all requests

---

# 5. API Design

The PayWay backend will expose REST-style API endpoints using HTTP/HTTPS.

All API responses will be formatted in **JSON**.

---

## Example Endpoints

### Authentication

POST /api/auth/login  
POST /api/auth/logout  
GET /api/auth/me

---

### Employee Management

GET /api/employees  
GET /api/employees/{id}

POST /api/employees  
PUT /api/employees/{id}  
DELETE /api/employees/{id}

---

### Salary Management

GET /api/salaries  
GET /api/salaries/{employeeId}

POST /api/salaries/update  
POST /api/salaries/apply-raises

---

### PTO Management

GET /api/pto/{employeeId}  
POST /api/pto/update

---

# 6. Database Architecture

The PayWay system will use a **PostgreSQL relational database hosted on AWS RDS** to store application data. The database will maintain employee records, salary information, system users, and audit logs.

The schema is designed to maintain **data integrity**, **support historical salary tracking**, and **enforce relationships between system entities**.

Key design considerations include:

- Use of **primary keys** to uniquely identify records
- Use of **foreign keys** to enforce relationships
- **Indexing** on frequently queried columns
- **Transaction support** for salary updates and payroll changes

---

# 6.1 Core Tables

The PayWay system will contain the following primary tables.

---

## Employees Table

Stores general employee information.

employees

---

employee_id (PK)  
first_name  
last_name  
email  
department_id (FK)  
hire_date  
termination_date  
status

Description:

- Each record represents a single employee.
- Employees belong to a department.
- Termination date is null for active employees.

---

## Departments Table

Stores organizational departments.

departments

---

department_id (PK)  
department_name

Description:

- Used to group employees by department.
- Enables salary comparisons and department-based raise calculations.

---

## Salaries Table

Stores current salary information.

salaries

---

salary_id (PK)  
employee_id (FK)  
salary_amount  
effective_date

Description:

- Stores the **current salary** for an employee.
- Each employee will have one active salary record.

---

## Salary History Table

Tracks historical salary changes.

salary_history

---

history_id (PK)  
employee_id (FK)  
old_salary  
new_salary  
change_percent  
change_reason  
changed_by  
change_date

Description:

- Records every salary modification.
- Supports auditing and compensation tracking.

---

## Performance Metrics Table

Stores employee performance information used for raise calculations.

performance_metrics

---

metric_id (PK)  
employee_id (FK)  
review_period  
performance_score  
ranking

Description:

- Used to determine department rankings.
- Supports automated raise calculations.

---

## PTO Records Table

Stores employee paid time off information.

pto_records

---

pto_id (PK)  
employee_id (FK)  
pto_days_available  
pto_days_used  
last_updated

Description:

- Tracks available and used PTO.
- Allows employees and HR to monitor time-off balances.

---

## Deductions Table

Stores payroll deduction records.

deductions

---

deduction_id (PK)  
employee_id (FK)  
deduction_type  
amount  
effective_date

Description:

- Tracks salary deductions such as benefits or tax adjustments.

---

## Users Table

Stores login credentials and account information.

users

---

user_id (PK)  
employee_id (FK)  
username  
password_hash  
role_id (FK)  
created_at

Description:

- Links application users to employee records.
- Supports system authentication.

---

## Roles Table

Defines system access roles.

roles

---

role_id (PK)  
role_name

Example roles:

1 - Employee  
2 - HR  
3 - Administrator

Description:

- Used for role-based access control.

---

## Audit Logs Table

Tracks important system actions.

audit_logs

---

log_id (PK)  
user_id (FK)  
action_type  
entity_modified  
entity_id  
timestamp  
description

Description:

- Maintains a history of system changes.
- Used for debugging, monitoring, and auditing.

---

# 6.2 Table Relationships

Key relationships between entities include:

Departments  
 |  
Employees  
 |  
Salaries  
 |  
Salary History

Employees  
 |  
Performance Metrics

Employees  
 |  
PTO Records

Employees  
 |  
Deductions

Employees  
 |  
Users  
 |  
Roles

Users  
 |  
Audit Logs

These relationships ensure that employee-related information is properly structured and that salary changes can be tracked over time.

---

# 6.3 Indexing Strategy

Indexes will be used to improve query performance on frequently accessed fields.

Examples include:

- `employee_id`
- `department_id`
- `review_period`
- `change_date`

Indexes will be created on columns commonly used in sorting, filtering, and joins.

---

# 7. Object-Oriented Design

The PayWay backend will follow **Object-Oriented Programming principles** to ensure modularity and maintainability.

Key principles used:

### Encapsulation

Classes manage their own data and behavior.

Example:

Employee  
Salary  
Department  
User

---

### Separation of Concerns

Each layer of the application has a distinct responsibility:

- Controllers handle requests
- Services implement business logic
- Repositories handle data access

---

### Reusability

Reusable services will be created for common operations such as:

- Authentication
- Salary calculations
- Audit logging

---

# 8. User Interface Design

The PayWay user interface will be built using **HTML and CSS**.

### UI Principles

- Clear navigation
- Minimal design for internal users
- Responsive layout
- Consistent styling using defined style guides

---

### Main Pages

The application will include the following interfaces:

Employee Dashboard

- View salary
- View salary history
- View PTO balance

HR Dashboard

- Employee management
- Salary updates
- Raise application tools

Admin Dashboard

- User management
- Role management
- System logs

---

# 9. Logging and Monitoring

PayWay will maintain logs to track system activity.

Logged events include:

- Salary updates
- Employee record modifications
- Authentication events
- System errors

Logs will include:

- Timestamp
- User performing the action
- Description of the change
- Affected system entity

---

# 10. Development Workflow

The PayWay project will be managed using **Jira**.

### Jira Usage

Jira will be used to manage:

- User stories
- Development tasks
- Bug tracking
- Feature requests

Development will follow an **iterative workflow** where features are implemented in small increments.

---

# 11. Deployment Environment

The PayWay system will be deployed using the following environment:

Application Server

- PHP web server (Apache or Nginx)

Database

- PostgreSQL hosted on AWS RDS

Security

- HTTPS enabled
- OAuth authentication

---

# 12. Future Enhancements

Potential future improvements include:

- Reporting dashboards
- Advanced performance analytics
- Payroll export functionality
- Integration with external HR systems
- Automated notification system for salary changes
