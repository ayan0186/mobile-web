document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        firstName: document.getElementById('firstName').value,
        // ... get other fields ...
    };

    const response = await fetch('register.php', {
        method: 'POST',
        body: JSON.stringify(formData)
    });
    
    const result = await response.json();
    if(result.success) window.location.href = 'sign-in.html';
});
