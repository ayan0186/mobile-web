// register.js - Handles registration functionality

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        hideError('errorMessage');
        
        const firstName = document.getElementById('firstName').value;
        const lastName = document.getElementById('lastName').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validate passwords match
        if (password !== confirmPassword) {
            showError('errorMessage', 'Passwords do not match');
            return;
        }
        
        // Validate phone number format (basic)
        const phoneRegex = /^\d{3}-\d{3}-\d{4}$/;
        if (!phoneRegex.test(phone)) {
            showError('errorMessage', 'Phone must be in format: 816-555-1234');
            return;
        }
        
        const result = await apiCall('/api/register', 'POST', {
            first_name: firstName,
            last_name: lastName,
            email: email,
            phone: phone,
            password: password
        });
        
        if (result.success) {
            // Registration successful - redirect to login
            showSuccess('successMessage', 'Registration successful! Redirecting to login...');
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        } else {
            showError('errorMessage', result.data.error || 'Registration failed');
        }
    });
});