/*
 * Dashboard page script
 * - Loads and renders tables for employees, salaries, and audit logs.
 * - Provides basic navigation between sections.
 */

const API = {
    employees: '../backend/api/dashboard.php',
    updateEmployee: '../backend/api/update_employee.php',
    salaries: '../backend/api/salaries.php',
    auditLogs: '../backend/api/audit_logs.php',
    applyRaises: '../backend/api/apply_raises.php',
    registerUser: '../backend/api/register_user.php',
    logout: '../backend/api/auth/logout.php',
    getCurrentUser: '../backend/api/auth/get_current_user.php',
};

let currentRole = null;

// Helper to fetch JSON and handle basic error paths
async function fetchJson(url, options = {}) {
    const res = await fetch(url, options);
    const payload = await res.json().catch(() => null);

    if (!res.ok) {
        throw new Error(payload?.message || 'Request failed');
    }

    return payload;
}

// Load the current user's role to determine access permissions
async function loadCurrentUser() {
    try {
        const res = await fetchJson(API.getCurrentUser);
        currentRole = res.role_id;
    } catch (err) {
        console.error('Failed to fetch current user role', err);
        alert('Failed to determine user role. Some features may be disabled.');
    }
}

// Render a list of employees into the employee table
async function loadEmployees() {
    const employees = await fetchJson(API.employees);
    const tbody = document.querySelector('#employee-table tbody');

    tbody.innerHTML = '';

    employees.forEach((emp) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${emp.first_name} ${emp.last_name}</td>
            <td>${emp.email}</td>
            <td>${emp.department_name}</td>
            <td>
                <button onclick="editEmployee(
                    ${emp.employee_id}, 
                    '${emp.first_name}', 
                    '${emp.last_name}', 
                    '${emp.email}', 
                    '${emp.department_id}')">
                    Edit
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

// Render current salaries and optional history into salary section
async function loadSalaries() {
    const data = await fetchJson(API.salaries);
    const tbodyCurrent = document.querySelector('#salary-table tbody');

    tbodyCurrent.innerHTML = '';

    data.current.forEach((s) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${s.first_name} ${s.last_name}</td>
            <td>$${s.salary_amount}</td>
            <td>${s.effective_date}</td>
        `;

        tbodyCurrent.appendChild(row);
    });

    // Recreate history table when needed (prevents duplicates)
    const existingHistory = document.querySelector('#salary-history-table');
    if (existingHistory) existingHistory.remove();

    if (!data.history || data.history.length === 0) return;

    const section = document.getElementById('salary-section');

    const historyTitle = document.createElement('h3');
    historyTitle.textContent = 'Salary History';
    section.appendChild(historyTitle);

    const historyTable = document.createElement('table');
    historyTable.id = 'salary-history-table';
    historyTable.classList.add('data-table');

    historyTable.innerHTML = `
        <thead>
            <tr>
                <th>Employee</th>
                <th>Old Salary</th>
                <th>New Salary</th>
                <th>Change %</th>
                <th>Change Date</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;

    section.appendChild(historyTable);

    const tbodyHistory = historyTable.querySelector('tbody');

    data.history.forEach((h) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${h.first_name} ${h.last_name}</td>
            <td>$${h.old_salary}</td>
            <td>$${h.new_salary}</td>
            <td>${h.change_percent != null ? h.change_percent + '%' : ''}</td>
            <td>${h.change_date}</td>
            <td>${h.change_reason || ''}</td>
        `;

        tbodyHistory.appendChild(row);
    });
}

// Render audit log table
async function loadAuditLogs() {
    const audits = await fetchJson(API.auditLogs);
    const tbody = document.querySelector('#audit-table tbody');

    tbody.innerHTML = '';

    audits.forEach((log) => {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${log.username ?? 'System'}</td>
            <td>${log.action_type}</td>
            <td>${log.entity_modified ?? ''}</td>
            <td>${log.entity_id ?? ''}</td>
            <td>${log.description ?? ''}</td>
            <td>${log.timestamp}</td>
        `;

        tbody.appendChild(row);
    });
}

// Apply raises and refresh salaries
async function applyRaises() {
    if (!confirm('Are you sure you want to apply department raises?')) return;

    const { message } = await fetchJson(API.applyRaises, { method: 'POST' });
    alert(message);
    loadSalaries();
}

// Editing an employee - pre-fills the edit form and shows the modal
function editEmployee(id, first, last, email, dept) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-first').value = first;
    document.getElementById('edit-last').value = last;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-department').value = dept;

    document.getElementById('edit-modal').style.display = 'block';
}

async function submitRegister() {
    if (!confirm('Are you sure you want to save these changes?')) return;

    const payload = {
        username: document.getElementById('register-username').value,
        password: document.getElementById('register-password').value,
        employee_id: document.getElementById('register-employee-id').value,
        role_id: parseInt(document.getElementById('register-role').value),
    };

    try {
        const res = await fetchJson(API.registerUser, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        alert(res.message);
        closeRegisterModal();
    } catch (err) {
        alert(err.message);
    }
}

async function submitEdit() {
    if (!confirm('Are you sure you want to save these changes?')) return;

    const payload = {
        employee_id: document.getElementById('edit-id').value,
        first_name: document.getElementById('edit-first').value,
        last_name: document.getElementById('edit-last').value,
        email: document.getElementById('edit-email').value,
        department_id: document.getElementById('edit-department').value,
    };

    try {
        const { message } = await fetchJson(API.updateEmployee, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        alert(message);

        closeEditEmployeeModal();

        loadEmployees();
    } catch (error) {
        alert(error.message);
    }
}

// Show the requested section and hide others
function showSection(sectionId) {
    ['employee-section', 'salary-section', 'audit-section'].forEach((id) => {
        document.getElementById(id).style.display =
            id === sectionId ? 'block' : 'none';
    });
}

function showEmployees() {
    showSection('employee-section');
}

function showSalaries() {
    showSection('salary-section');
    loadSalaries();
}

function showAuditLogs() {
    showSection('audit-section');
    loadAuditLogs();
}

function showRegisterUser() {
    if (currentRole !== 3) {
        alert('Unauthorized: Only Admins can register new users.');
        return;
    }
    document.getElementById('register-modal').style.display = 'block';
}

function closeEditEmployeeModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

function closeRegisterModal() {
    document.getElementById('register-modal').style.display = 'none';
}

// Log the current user out
async function logout() {
    if (!confirm('Are you sure you want to logout?')) return;

    try {
        const data = await fetchJson(API.logout, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        });

        alert(data.message);
        window.location.href = 'login.html';
    } catch (error) {
        console.error('Logout failed', error);
        alert('Failed to logout. Please try again.');
    }
}

// Initial load
(async function init() {
    try {
        await loadCurrentUser(); // load role first
        await loadEmployees(); // then load employees
    } catch (err) {
        console.error('Initialization failed:', err);
        alert('Failed to load dashboard. Some features may not work.');
    }
})();
