// dashboard.js - Handles dashboard functionality

document.addEventListener('DOMContentLoaded', async function() {
    await loadAppointments();
});

// ── Appointments ──────────────────────────────────────────

async function loadAppointments() {
    const result = await apiCall('/api/appointments/my');

    if (result.success) {
        displayAppointments(result.data);
    } else {
        showError('appointmentsError', 'Failed to load appointments');
    }
}

function displayAppointments(appointments) {
    const listDiv = document.getElementById('appointmentsList');

    if (appointments.length === 0) {
        listDiv.innerHTML = '<p class="text-muted">No appointments yet. Book your first appointment!</p>';
        return;
    }

    let html = `
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Services</th>
                    <th>Beautician</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;

    appointments.forEach(apt => {
        const canCancel = apt.Status === 'scheduled' && new Date(apt.Appointment_Date) > new Date();

        html += `
            <tr>
                <td>${formatDate(apt.Appointment_Date)}</td>
                <td>${formatTime(apt.Appointment_Time)}</td>
                <td>${apt.Services}</td>
                <td>${apt.Beautician_Name || 'No preference'}</td>
                <td><span class="badge bg-${getStatusColor(apt.Status)}">${apt.Status}</span></td>
                <td>$${parseFloat(apt.Total_Price).toFixed(2)}</td>
                <td>
                    ${canCancel ? `<button class="btn btn-sm btn-danger" onclick="cancelAppointment(${apt.AppointmentID})">Cancel</button>` : '-'}
                </td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    listDiv.innerHTML = html;
}

async function cancelAppointment(appointmentId) {
    if (!confirm('Are you sure you want to cancel this appointment?')) return;

    const result = await apiCall(`/api/appointments/${appointmentId}/cancel`, 'PUT');

    if (result.success) {
        showSuccess('successMessage', 'Appointment cancelled successfully');
        await loadAppointments();
    } else {
        showError('errorMessage', result.data.error || 'Failed to cancel appointment');
    }
}

// ── Helpers ───────────────────────────────────────────────

function getStatusColor(status) {
    const colors = {
        'scheduled': 'primary',
        'completed': 'success',
        'cancelled': 'secondary',
        'no-show':   'warning'
    };
    return colors[status] || 'secondary';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTime(timeStr) {
    if (!timeStr) return '-';
    // timeStr comes as "HH:MM:SS" from MySQL
    const [hours, minutes] = timeStr.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour12 = h % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
}

// ── Logout ────────────────────────────────────────────────

async function logout() {
    const result = await apiCall('/api/logout', 'POST');

    if (result.success) {
        window.location.href = '/sign-in';
    } else {
        alert('Logout failed, please try again');
    }
}