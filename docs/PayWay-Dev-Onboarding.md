# PayWay Developer Onboarding

This document is to help anyone who wants to add to the project, to be able to get set up and going quickly with confidence.

---

## 1) What is PayWay?

PayWay is a simple employee management system focused on salary tracking and raise administration. It includes:

- A **PHP backend** exposing JSON API endpoints (no framework).
- A **Vanilla HTML/JS frontend** that consumes the API.
- A **database schema** that tracks employees, salaries, and audit history.

Key themes: **security**, **role-based access**, **audit logging**, and **easy maintainability**.

---

## 2) High-level architecture

### Backend (`backend/`)

- **`api/`** - API endpoints that power the frontend.
- **`config/`** - Database + environment loading logic.
- **`db/`** - Schema definition scripts.
- **`utils/`** - Shared helpers (validation, etc.).

The backend uses PHP sessions for authentication and reads `.env` variables via `vlucas/phpdotenv`.

### Frontend (`frontend/`)

- HTML pages (login + dashboard), with JS modules to call the API.
- Basic UI components for authentication, table rendering, modal handling, etc.

---

## 3) Getting set up locally

### 1) Clone and install

```bash
git clone <your-repo-url>
cd PayWay
composer install -d backend
npm install
```

> 🧹 **Code formatting:** this repo uses Prettier for consistent formatting. Run:
>
> ```bash
> npx prettier --write .
> ```
>
> This is especially helpful after editing docs or frontend JS.

### 2) Database

1. Create a database (Postgres).
2. Add tables from schema.sql

> The app expects a working `salaries` / `salary_history` table structure; the schema file is the source of truth.

### 3) Configure environment

Create a .env in the project root with your credentials.

Example:

```env
PORT=8000
DB_HOST=localhost
DB_PORT=5432
DB_NAME=payway
DB_USER=payway_user
DB_PASSWORD=supersecret
JWT_SECRET=supersecretkey
```

### 4) Run the backend

```bash
cd backend
php -S localhost:8000
```

Then open the frontend:

```
http://localhost:8000/frontend/login.html
```

---

## 4) Key code paths to know

### Authentication

- `backend/api/auth/login.php` - handles credential validation
- `backend/api/auth/logout.php` - destroys the session
- `backend/api/auth/get_current_user.php` - returns the current session user

### Role-based access

- `backend/api/auth/auth.php` - included by most endpoints to enforce session/auth rules
- Role IDs are used to gate access (e.g., `role_id == 1` is a regular employee and cannot apply raises)

### Applying raises + audit logging

- `backend/api/apply_raises.php` - computes new salary values using SQL window functions and inserts both new salary rows and `salary_history` record(s).
- Audit logs are stored in `salary_history` with `changed_by`.

### Validation

- `backend/utils/validation.php` - shared input validation utilities used during sign-in/registration and other API actions.

---

## 5) Common tasks workflow

- **Add a new API endpoint**: Add a PHP file under `backend/api/`, include `auth/auth.php`, and return JSON.
- **Update schema**: Edit `backend/db/schema.sql` and apply locally.
- **Fix frontend bugs**: Update the relevant JS module in `frontend/js/` (e.g., `api.js`, `dashboard.js`).
- **Add logging**: Extend the `salary_history` or add a new audit table, and log on critical actions.

---

## 6) Testing

- There is a minimal PHPUnit setup in `backend/tests/`.
- Run tests via:

```bash
cd backend
./vendor/bin/phpunit
```

---

## 7) Notes on security & maintainability

- **Use RBAC**: Most endpoints check `$_SESSION['role_id']` before allowing actions.
- **Validate everything**: All incoming request payloads should be validated (see `utils/validation.php`).
- **Keep frontend/api contract stable**: The frontend depends on predictable JSON models, so avoid breaking changes without bumping the contract.

---

## 8) Where to ask questions

If you're unsure about a piece of logic or where to make a change:

1. Search for the relevant file in `backend/api/` or `frontend/js/`.
2. Look for similar patterns (e.g., other endpoints using `auth/auth.php`).
3. Feel free to reach out to me on LinkedIn: https://www.linkedin.com/in/yasirazizi/.
