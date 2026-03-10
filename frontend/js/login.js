/**
 * Handles user login by sending credentials to the backend API.
 * Displays success or error messages based on the response.
 */
async function login() {
    // Get form input values
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Send login request to backend
    const response = await fetch('../../backend/api/auth/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: username,
            password: password,
        }),
    });

    // Parse JSON response
    const data = await response.json();

    const messageDiv = document.getElementById('login-message');

    // Handle successful login
    if (response.ok) {
        messageDiv.innerText = 'Login successful';
        // redirect to dashboard
        window.location.href = 'dashboard.html';
    } else {
        // Display error message from server
        messageDiv.innerText = data.message;
    }
}
