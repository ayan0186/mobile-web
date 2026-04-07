// Helper functions
function showError(id, message) {
    const el = document.getElementById(id);
    el.textContent = message;
    el.style.display = 'block';
}

function hideError(id) {
    const el = document.getElementById(id);
    el.style.display = 'none';
}

async function apiCall(url, method, body) {
    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await response.json();
        return { success: response.ok, data: data };
    } catch (error) {
        return { success: false, data: { error: 'Network error, please try again' } };
    }
}

// Login form handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        hideError('errorMessage');

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const result = await apiCall('/api/login', 'POST', { email, password });

        if (result.success) {
            window.location.href = '/dashboard';
        } else {
            showError('errorMessage', result.data.error || 'Login failed');
        }
    });
});