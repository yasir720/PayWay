# PayWay

A lightweight employee management system built with PHP (backend) and plain HTML/JS (frontend). PayWay allows you to track employees, update salaries, and apply raises using an audit-log-friendly workflow.

## ✅ Key Features

- Add and manage employees through a clean API layer
- Apply salary raises with an audit trail (audit logs track who made what change and when)
- Role-based access control (RBAC) to limit actions to authorized roles (e.g., admin vs regular user)
- Session management with configurable timeouts to reduce risk from unattended sessions
- Input validation for sign-in/registration and all API endpoints to prevent malformed requests
- Clean separation of frontend dashboard and backend API for easier maintenance and testing

## 🎬 Demo

<video src="./videos/PayWay-Demo.mp4" width="320" height="240" controls></video>

## 🧰 Tech Stack

- **Backend**: PHP (no framework, simple API endpoints)
- **Frontend**: Vanilla HTML/CSS/JavaScript

## 🚀 Getting Started

### 1) Clone the repo

```bash
git clone https://github.com/yasir720/PayWay.git
cd PayWay
```

### 2) Set up the database

- Create a database for the app (PostgreSQL).
- Apply the schema from `backend/db/schema.sql`.

> Tip: AWS RDS was used during development, but any compatible SQL database should work.

### 3) Configure environment variables

Create a `.env` file in the **project root** (next to `README.md`) and set your database credentials.

Example `.env`:

```env
PORT=8000

DB_HOST=localhost
DB_PORT=5432
DB_NAME=payway
DB_USER=payway_user
DB_PASSWORD=supersecret

# Optional (for Postgres SSL)
sslmode=disable
sslrootcert=

JWT_SECRET=supersecretkey
```

### 4) Install dependencies

```bash
composer install -d backend
npm install
```

### 5) Run the backend server

```bash
cd backend
php -S localhost:8000
```

### 6) Open the app

Open the frontend in your browser:

```
http://localhost:8000/frontend/login.html
```

## 🏗️ Want to Contribute?

Thanks for wanting to help improve PayWay! A few quick ways to get rolling:

- **Read the onboarding doc:** `docs/DEV-ONBOARDING.md` has architecture, setup, and key code paths.
- **Run the app locally** (see “Getting Started” above) so you can reproduce and test changes.
- **Use a branch/PR workflow:** Create a feature branch, commit early/often, and open a pull request.

---
