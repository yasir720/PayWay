/*
 * Dashboard script.
 * Manages employee, salary, and audit views.
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

// Helper to fetch JSON and throw on HTTP errors.
async function fetchJson(url, options = {}) {
    const res = await fetch(url, options);
    const payload = await res.json().catch(() => null);

    if (!res.ok) {
        throw new Error(payload?.message || 'Request failed');
    }

    return payload;
}

// Load the current user's role for permission checks.
async function loadCurrentUser() {
    try {
        const res = await fetchJson(API.getCurrentUser);
        currentRole = res.role_id;
    } catch (err) {
        console.error('Failed to fetch current user role', err);
        alert('Failed to determine user role. Some features may be disabled.');
    }
}

// Render employee list into the table.
async function loadEmployees() {
    const employees = await api.fetchJson('employees');
    employeeTable.renderRows(
        employees,
        (emp) => `
        <td>${emp.first_name} ${emp.last_name}</td>
        <td>${emp.email}</td>
        <td>${emp.department_name}</td>
        <td>
            <button onclick="editEmployee(${emp.employee_id}, '${emp.first_name}', '${emp.last_name}', '${emp.email}', '${emp.department_id}')">Edit</button>
        </td>
    `,
    );
}

// Render current salaries and optional history.
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

    // Recreate history table to avoid duplicates.
    const existingHistory = document.querySelector('#salary-history-table');
    if (existingHistory) existingHistory.remove();

    if (data.history && data.history.length) {
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
                    <th>Employee</th><th>Old Salary</th><th>New Salary</th><th>Change %</th><th>Change Date</th><th>Reason</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;

        section.appendChild(historyTable);
        const tbody = historyTable.querySelector('tbody');

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
            tbody.appendChild(row);
        });
    }
}

// Render audit log entries.
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

// Apply raises and refresh salary view.
async function applyRaises() {
    if (!Notifier.confirm('Are you sure you want to apply department raises?'))
        return;
    const { message } = await api.fetchJson('applyRaises', { method: 'POST' });
    Notifier.alert(message);
    loadSalaries();
}

// Prefill edit modal with employee data.
function editEmployee(id, first, last, email, dept) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-first').value = first;
    document.getElementById('edit-last').value = last;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-department').value = dept;
    modalManager.open('edit-modal');
};

async function submitEdit() {
    if (!Notifier.confirm('Are you sure you want to save these changes?'))
        return;

    const payload = {
        employee_id: document.getElementById('edit-id').value,
        first_name: document.getElementById('edit-first').value,
        last_name: document.getElementById('edit-last').value,
        email: document.getElementById('edit-email').value,
        department_id: document.getElementById('edit-department').value,
    };

    try {
        const { message } = await api.fetchJson('updateEmployee', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        Notifier.alert(message);
        modalManager.close('edit-modal');
        loadEmployees();
    } catch (error) {
        Notifier.alert(error.message);
    }
}

// Switch dashboard sections.
function showSection(sectionId) {
    ['employee-section', 'salary-section', 'audit-section'].forEach((id) => {
        document.getElementById(id).style.display =
            id === sectionId ? 'block' : 'none';
    });
}

    const payload = {
        username: document.getElementById('register-username').value,
        password: document.getElementById('register-password').value,
        employee_id: document.getElementById('register-employee-id').value,
        role_id: parseInt(document.getElementById('register-role').value),
    };

    try {
        const res = await api.fetchJson('registerUser', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        Notifier.alert(res.message);
        modalManager.close('register-modal');
    } catch (err) {
        Notifier.alert(err.message);
    }
}

function showSection(section) {
    ui.show(section);
}

function showRegisterUser() {
    if (!userSession.isAdmin()) {
        Notifier.alert('Unauthorized: Only Admins can register new users.');
        return;
    }
    modalManager.open('register-modal');
}

async function logout() {
    if (!Notifier.confirm('Are you sure you want to logout?')) return;

    try {
        const data = await api.fetchJson('logout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        });
        Notifier.alert(data.message);
        window.location.href = 'login.html';
    } catch (error) {
        console.error('Logout failed', error);
        Notifier.alert('Failed to logout. Please try again.');
    }
}

// Initialize dashboard on page load.
(async function init() {
    try {
        await userSession.loadCurrentUser(api);
        await loadEmployees();
    } catch (err) {
        console.error('Initialization failed:', err);
        Notifier.alert('Failed to load dashboard. Some features may not work.');
    }
})();

// --- Expose actions globally for buttons ---
window.showEmployees = () => showSection('employees');
window.showSalaries = () => {
    showSection('salary');
    loadSalaries();
};
window.showAuditLogs = () => {
    showSection('audit');
    loadAuditLogs();
};
window.applyRaises = applyRaises;
window.submitEdit = submitEdit;
window.submitRegister = submitRegister;
window.showRegisterUser = showRegisterUser;
window.logout = logout;
window.closeEditEmployeeModal = () => modalManager.close('edit-modal');
window.closeRegisterModal = () => modalManager.close('register-modal');
