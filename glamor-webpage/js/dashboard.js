// Dashboard functionality

document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        window.location.href = 'sign-in.html';
        return;
    }
    
    // Display welcome message
    const welcomeName = document.getElementById('welcomeName');
    if (welcomeName) {
        welcomeName.textContent = 'Welcome, User ' + getUserId();
    }
    
    // Load appointments
    loadAppointments();
});

function loadAppointments() {
    const appointmentsList = document.getElementById('appointmentsList');
    const errorDiv = document.getElementById('appointmentsError');
    
    fetch('get-appointments.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.appointments && data.appointments.length > 0) {
                let html = '<table class="table"><thead><tr><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody>';
                
                data.appointments.forEach(apt => {
                    html += `<tr>
                        <td>${apt.Appointment_Date}</td>
                        <td>${apt.Appointment_Time}</td>
                        <td>${apt.Status}</td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                appointmentsList.innerHTML = html;
            } else {
                appointmentsList.innerHTML = '<p>No appointments scheduled yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorDiv.textContent = 'Failed to load appointments';
            errorDiv.style.display = 'block';
        });
}

