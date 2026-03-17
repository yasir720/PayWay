# PayWay

## System Design Document (SDD)

Version: 1.1  
Author: Yasir Alizai  
Date: March 2026

---

# 1. Introduction

## 1.1 Purpose

This document describes the technical design and architecture of **PayWay**, a salary management system used to manage employee salary data, track compensation history, and administer employee records securely.

The document outlines the technologies used, system architecture, data design, and API structure that will support the implementation of the system.

---

# 1.2 Technology Stack

PayWay currently uses the following technologies:

- **PHP 8+** — backend API implementation (procedural PHP scripts)
- **PostgreSQL** — relational database (schema in `backend/db/schema.sql`)
- **HTML / CSS / JavaScript** — frontend UI (static files in `frontend/`)
- **PDO** — database access layer
- **PHP sessions** — authentication state is stored in server-side sessions
- **bcrypt** — password hashing (via `password_hash` / `password_verify`)

---

# 2. System Architecture

PayWay is implemented as a web application with a browser-based frontend talking to a PHP backend over HTTP, with persistence in PostgreSQL.

```
Browser (HTML/JS)
  ↕
HTTP/HTTPS (cookie-based session)
  ↕
PHP API endpoints (backend/api/*.php)
  ↕
PostgreSQL (backend/db/schema.sql)
```

### Components

**Client Layer**

- Static HTML pages and JavaScript
- UI interactions and API calls

**Application Layer**

- Procedural PHP scripts implementing API endpoints
- Session-based authentication and role enforcement
- Business logic for salaries, raises, and user management

**Data Layer**

- PostgreSQL database (configured via `backend/config/env.php`)
- Stores employees, salaries, users, roles, and audit logs

---

# 3. Application Architecture

The backend is organized as a set of modular, procedural PHP scripts. Each endpoint in `backend/api/` acts as a controller that handles a specific responsibility.

### Main Application Layers

- API endpoints (controllers)
- Business logic (embedded in endpoint scripts)
- Database access via PDO
- PostgreSQL database

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

PayWay uses **session-based authentication** implemented with PHP sessions.

### Authentication Flow

1. Client sends credentials to `POST /backend/api/auth/login.php`.
2. Backend validates the username/password (bcrypt) and creates a session.
3. A secure, HttpOnly cookie is returned to the client.
4. Subsequent API requests include the session cookie and are authenticated server-side.

### Security Controls

- Passwords are stored as bcrypt hashes.
- Sessions expire after **2 hours** and idle out after **20 minutes**.
- Role-based access control is enforced on API endpoints.

---

# 5. API Design

The backend exposes a set of JSON APIs under `backend/api/`.

All APIs (except login) require an active session cookie.

---

## Authentication

- `POST /backend/api/auth/login.php` — payload: `{ username, password }`
- `POST /backend/api/auth/logout.php`
- `GET /backend/api/auth/get_current_user.php`

---

## Dashboard / Employee Data

- `GET /backend/api/dashboard.php` — returns employees (current user for Employees)
- `POST /backend/api/update_employee.php` — updates employee information (role restricted)

---

## Salaries

- `GET /backend/api/salaries.php` — returns current salary data and salary history (history scope depends on role)
- `POST /backend/api/apply_raises.php` — applies department-based raises (HR/Admin only)

---

## User Management

- `POST /backend/api/register_user.php` — creates a user account (Admin only)

---

## Auditing

- `GET /backend/api/audit_logs.php` — returns recent audit logs (Admin only)

---

# 6. Database Architecture

The PayWay system uses a **PostgreSQL relational database** to store application data. The database maintains employee records, salary information, system users, and audit logs.

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

# 7. Code Organization

The backend is organized into modular, procedural scripts with shared helpers.

### Shared Components

- `backend/config/database.php`: PDO database connection and configuration.
- `backend/utils/validation.php`: input validation helpers.
- `backend/api/auth/auth.php`: session validation and timeout enforcement.

### Separation of Concerns

Each API script focuses on a single responsibility:

- Authentication endpoints (`backend/api/auth/*.php`)
- Employee and salary management (`backend/api/dashboard.php`, `backend/api/salaries.php`, etc.)
- Audit logging (`backend/api/audit_logs.php`)

This structure keeps business logic close to the API surface while keeping shared utilities in common files.

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

The frontend is a single-page dashboard (`frontend/dashboard.html`) that adapts to the logged-in user’s role.

- **Employee view:** view personal salary information.
- **HR view:** view all employees, edit employee details (with restrictions), view current salaries and salary history, and apply raises.
- **Admin view:** all HR functionality plus user registration and viewing audit logs.

The dashboard uses modals for editing employee information and registering new users.

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

The PayWay system can be deployed using a standard PHP web server and a PostgreSQL database host.

### Application Server

- PHP 8+ (Apache, Nginx, or PHP built-in server)

### Database

- PostgreSQL (self-hosted or managed)

### Security

- HTTPS is recommended for production deployments
- Authentication is handled via secure PHP sessions (cookie-based)

---

# 12. Future Enhancements

Potential future improvements include:

- Reporting dashboards
- Advanced performance analytics
- Payroll export functionality
- Integration with external HR systems
- Automated notification system for salary changes
