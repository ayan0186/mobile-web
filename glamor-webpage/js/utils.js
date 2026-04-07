// utils.js - Shared helper functions for all pages

async function apiCall(url, method = 'GET', body = null) {
    try {
        const options = {
            method: method,
            headers: { 'Content-Type': 'application/json' }
        };
        if (body) options.body = JSON.stringify(body);

        const response = await fetch(url, options);
        const data = await response.json();
        return { success: response.ok, data: data };
    } catch (error) {
        return { success: false, data: { error: 'Network error, please try again' } };
    }
}

function showError(id, message) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = message;
    el.style.display = 'block';
}

function showSuccess(id, message) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = message;
    el.style.display = 'block';
}

function hideError(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'none';
}