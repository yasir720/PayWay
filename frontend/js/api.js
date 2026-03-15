/*
 * API interactions
 */

export class DashboardAPI {
    constructor(endpoints) {
        this.endpoints = endpoints;
    }

    async fetchJson(endpoint, options = {}) {
        const res = await fetch(this.endpoints[endpoint], options);
        const payload = await res.json().catch(() => null);
        if (!res.ok) throw new Error(payload?.message || 'Request failed');
        return payload;
    }
}

export const API = {
    employees: '../backend/api/dashboard.php',
    updateEmployee: '../backend/api/update_employee.php',
    salaries: '../backend/api/salaries.php',
    auditLogs: '../backend/api/audit_logs.php',
    applyRaises: '../backend/api/apply_raises.php',
    registerUser: '../backend/api/register_user.php',
    logout: '../backend/api/auth/logout.php',
    getCurrentUser: '../backend/api/auth/get_current_user.php',
};