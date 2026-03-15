/*
 * main dashboard page
 * Handles data fetching, UI updates, and user interactions
 */

import { DashboardAPI, API } from './api.js';
import { UserSession } from './userSession.js';
import { TableRenderer } from './tableRenderer.js';
import { ModalManager } from './modalManager.js';
import { DashboardUI } from './dashboardUI.js';
import { Notifier } from './notifier.js';

// Instantiate core objects
const api = new DashboardAPI(API);
const userSession = new UserSession();
const modalManager = new ModalManager();
const ui = new DashboardUI({
    employees: document.getElementById('employee-section'),
    salary: document.getElementById('salary-section'),
    audit: document.getElementById('audit-section'),
});

// Register modals
modalManager.register('edit-modal');
modalManager.register('register-modal');

// Tables
const employeeTable = new TableRenderer('#employee-table');
const salaryTable = new TableRenderer('#salary-table');
const auditTable = new TableRenderer('#audit-table');

// --- Data Loaders ---
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

async function loadSalaries() {
    const data = await api.fetchJson('salaries');

    // Current
    salaryTable.renderRows(
        data.current,
        (s) => `
        <td>${s.first_name} ${s.last_name}</td>
        <td>$${s.salary_amount}</td>
        <td>${s.effective_date}</td>
    `,
    );

    // History
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

async function loadAuditLogs() {
    const audits = await api.fetchJson('auditLogs');
    auditTable.renderRows(
        audits,
        (log) => `
        <td>${log.username ?? 'System'}</td>
        <td>${log.action_type}</td>
        <td>${log.entity_modified ?? ''}</td>
        <td>${log.entity_id ?? ''}</td>
        <td>${log.description ?? ''}</td>
        <td>${log.timestamp}</td>
    `,
    );
}

// --- Actions ---
async function applyRaises() {
    if (!Notifier.confirm('Are you sure you want to apply department raises?'))
        return;
    const { message } = await api.fetchJson('applyRaises', { method: 'POST' });
    Notifier.alert(message);
    loadSalaries();
}

// Employee edit modal
window.editEmployee = function (id, first, last, email, dept) {
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

async function submitRegister() {
    if (!Notifier.confirm('Are you sure you want to save these changes?'))
        return;

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

// --- Initialization ---
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
