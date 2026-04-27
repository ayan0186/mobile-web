document.addEventListener('DOMContentLoaded', function() {
    if (!isLoggedIn()) {
        window.location.href = 'sign-in.html';
        return;
    }
    
    const welcomeName = document.getElementById('welcomeName');
    if (welcomeName) {
        welcomeName.textContent = 'Welcome, User ' + localStorage.getItem('customer_id');
    }
    
    loadAppointments();
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('rescheduleDate').min = today;
});

let appointmentsData = {};

function loadAppointments() {
    const appointmentsList = document.getElementById('appointmentsList');
    const errorDiv = document.getElementById('appointmentsError');
    
    // ✅ Use customer_id
    fetch('get-appointments.php?customer_id=' + localStorage.getItem('customer_id'))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.appointments && data.appointments.length > 0) {
                appointmentsData = {};
                let html = '<table class="table table-striped"><thead><tr><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                
                data.appointments.forEach(apt => {
                    appointmentsData[apt.AppointmentID] = apt;
                    html += `<tr>
                        <td>${apt.Appointment_Date}</td>
                        <td>${apt.Appointment_Time}</td>
                        <td><span class="badge bg-info">${apt.Status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="openRescheduleModal(${apt.AppointmentID})">Reschedule</button>
                            <button class="btn btn-sm btn-danger" onclick="openDeleteAppointmentModal(${apt.AppointmentID})">Delete</button>
                        </td>
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

function openRescheduleModal(appointmentId) {
    const apt = appointmentsData[appointmentId];
    if (!apt) { alert('Unable to load appointment details'); return; }
    
    document.getElementById('rescheduleAppointmentId').value = appointmentId;
    document.getElementById('rescheduleDate').value = apt.Appointment_Date;
    document.getElementById('rescheduleTime').value = apt.Appointment_Time;
    document.getElementById('rescheduleError').style.display = 'none';
    
    new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
}

function submitReschedule() {
    const appointmentId = document.getElementById('rescheduleAppointmentId').value;
    const newDate       = document.getElementById('rescheduleDate').value;
    const newTime       = document.getElementById('rescheduleTime').value;
    const errorDiv      = document.getElementById('rescheduleError');
    const apt           = appointmentsData[appointmentId];
    
    if (!newDate || !newTime) {
        errorDiv.textContent = 'Please fill in both date and time';
        errorDiv.style.display = 'block';
        return;
    }
    
    if (newDate === apt.Appointment_Date && newTime === apt.Appointment_Time) {
        errorDiv.textContent = 'Please change the date or time';
        errorDiv.style.display = 'block';
        return;
    }
    
    fetch('update-appointment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            appointmentId: appointmentId,
            date: newDate,
            time: newTime,
            customer_id: localStorage.getItem('customer_id') // ✅ use customer_id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('rescheduleModal')).hide();
            showSuccessMessage('Appointment rescheduled successfully!');
            loadAppointments();
        } else {
            errorDiv.textContent = data.message || 'Failed to reschedule appointment';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred while rescheduling';
        errorDiv.style.display = 'block';
    });
}

function openDeleteAppointmentModal(appointmentId) {
    document.getElementById('deleteAppointmentId').value = appointmentId;
    document.getElementById('deleteAppointmentError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('deleteAppointmentModal')).show();
}

function submitDeleteAppointment() {
    const appointmentId = document.getElementById('deleteAppointmentId').value;
    const errorDiv      = document.getElementById('deleteAppointmentError');
    
    fetch('cancel-appointment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            appointmentId: appointmentId,
            customer_id: localStorage.getItem('customer_id') // ✅ use customer_id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('deleteAppointmentModal')).hide();
            showSuccessMessage('Appointment deleted successfully!');
            loadAppointments();
        } else {
            errorDiv.textContent = data.message || 'Failed to delete appointment';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred while deleting';
        errorDiv.style.display = 'block';
    });
}

function openDeleteAccountModal() {
    document.getElementById('accountPassword').value = '';
    document.getElementById('deleteAccountError').style.display = 'none';
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
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
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            password: password,
            user_id: localStorage.getItem('user_id'),
            customer_id: localStorage.getItem('customer_id') // ✅ send both
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('user_id');
            localStorage.removeItem('customer_id');
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

function showSuccessMessage(message) {
    const successDiv = document.getElementById('successMessage');
    if (successDiv) {
        successDiv.textContent = message;
        successDiv.style.display = 'block';
        setTimeout(() => { successDiv.style.display = 'none'; }, 5000);
    }
}