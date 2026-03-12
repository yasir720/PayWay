/**
 * Handles user login by sending credentials to the backend API.
 * Displays success or error messages based on the response.
 */
async function login() {
    // Get form input values
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const messageDiv = document.getElementById('login-message');

    // Send login request to backend
    try {
        const response = await fetch('../../backend/api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password }),
        });

        // Parse JSON response
        const data = await response.json();

        // Handle successful login
        if (response.ok) {
            messageDiv.innerText = 'Login successful';
            // redirect to dashboard
            window.location.href = 'dashboard.html';
        } else {
            messageDiv.innerText = data.message || 'Login failed';
        }
    } catch (err) {
        console.error(err);
        messageDiv.innerText = 'An unexpected error occurred';
    }
}
