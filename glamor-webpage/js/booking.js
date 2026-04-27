document.addEventListener('DOMContentLoaded', function() {
    if (!isLoggedIn()) {
        window.location.href = 'sign-in.html';
        return;
    }
    
    const customerName = document.getElementById('customerName');
    if (customerName) {
        customerName.textContent = 'User ' + localStorage.getItem('customer_id');
    }
    
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', handleBooking);
    }
});

async function handleBooking(e) {
    e.preventDefault();
    
    const date      = document.getElementById('appointmentDate').value;
    const time      = document.getElementById('appointmentTime').value;
    const beautician = document.getElementById('beauticianSelect').value;
    const errorMessage   = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');
    
    try {
        const response = await fetch('process-booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                appointmentDate:  date,
                appointmentTime:  time,
                beauticianSelect: beautician,
                customer_id: localStorage.getItem('customer_id') // ✅ use customer_id
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            successMessage.textContent = result.message;
            successMessage.style.display = 'block';
            errorMessage.style.display = 'none';
            document.getElementById('bookingForm').reset();
            setTimeout(() => { window.location.href = 'dashboard.html'; }, 2000);
        } else {
            errorMessage.textContent = result.message || 'Booking failed';
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
        }
    } catch (error) {
        console.error('Error:', error);
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.style.display = 'block';
        successMessage.style.display = 'none';
    }
}