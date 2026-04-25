// Utility functions for the application

function showError(message) {
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
    }
}

function hideError() {
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}

function redirect(url) {
    window.location.href = url;
}

// Check if user is logged in
function isLoggedIn() {
    // This would typically check a session on the server
    // For now, we can use localStorage as a simple check
    return localStorage.getItem('user_id') !== null;
}

// Store user info after login
function setUserInfo(userId, role) {
    localStorage.setItem('user_id', userId);
    localStorage.setItem('role', role);
}

// Clear user info on logout
function clearUserInfo() {
    localStorage.removeItem('user_id');
    localStorage.removeItem('role');
}

// Get current user ID
function getUserId() {
    return localStorage.getItem('user_id');
}

// Get current user role
function getUserRole() {
    return localStorage.getItem('role');
}

// Logout function
function logout() {
    clearUserInfo();
    window.location.href = 'sign-in.html';
}
