/*
 * managing user session and role information
 */
export class UserSession {
    constructor() {
        this.role = null;
    }

    async loadCurrentUser(api) {
        try {
            const res = await api.fetchJson('getCurrentUser');
            this.role = res.role_id;
        } catch (err) {
            console.error('Failed to fetch current user', err);
            alert('Failed to determine user role. Some features may be disabled.');
        }
    }

    isAdmin() {
        return this.role === 3; // assuming 3 = Admin
    }
}