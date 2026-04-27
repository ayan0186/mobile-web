// Profile page functionality

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        window.location.href = 'sign-in.html';
        return;
    }
    
    // Load user information
    loadUserInfo();
});

function loadUserInfo() {
    // ✅ Fixed: pass user_id as query param instead of relying on session
    fetch('get-user.php?user_id=' + localStorage.getItem('user_id'))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                const user = data.user;
                document.getElementById('userName').textContent = user.FirstName + ' ' + user.LastName;
                document.getElementById('userEmail').textContent = user.Email;
                document.getElementById('userPhone').textContent = user.Phone || 'Not provided';
            } else {
                showError('Failed to load user information');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred while loading user information');
        });
}

function openDeleteAccountModal() {
    document.getElementById('accountPassword').value = '';
    document.getElementById('deleteAccountError').style.display = 'none';
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    deleteModal.show();
}

function submitDeleteAccount() {
    const password = document.getElementById('accountPassword').value;
    const errorDiv = document.getElementById('deleteAccountError');
    
    if (!password) {
        errorDiv.textContent = 'Please enter your password';
        errorDiv.style.display = 'block';
        return;
    }
    
    fetch('delete-account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            password: password,
            user_id: localStorage.getItem('user_id') // ✅ Fixed: send user_id from localStorage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // ✅ Fixed: clear localStorage instead of calling clearUserInfo()
            localStorage.removeItem('user_id');
            localStorage.removeItem('role');
            alert('Account deleted successfully. You will be redirected to the login page.');
            window.location.href = 'sign-in.html';
        } else {
            errorDiv.textContent = data.message || 'Failed to delete account';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred while deleting your account';
        errorDiv.style.display = 'block';
    });
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}