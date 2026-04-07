// booking.js - Handles appointment booking functionality

let services = [];

document.addEventListener('DOMContentLoaded', async function() {
    setupDateRestrictions();
    await loadServices();
    await loadBeauticians();
});

function setupDateRestrictions() {
    const dateInput = document.getElementById('appointmentDate');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
    
    // Set maximum date to 90 days from now
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 90);
    dateInput.setAttribute('max', maxDate.toISOString().split('T')[0]);
}

async function loadServices() {
    const result = await apiCall('/api/services');
    
    if (result.success) {
        services = result.data;
        displayServices(services);
    } else {
        showError('errorMessage', 'Failed to load services');
    }
}

function displayServices(servicesList) {
    const servicesDiv = document.getElementById('servicesList');
    servicesDiv.innerHTML = '';
    
    servicesList.forEach(service => {
        const div = document.createElement('div');
        div.className = 'form-check';
        div.innerHTML = `
            <input class="form-check-input service-checkbox" 
                   type="checkbox" 
                   value="${service.ServiceID}" 
                   id="service${service.ServiceID}" 
                   data-price="${service.Price}">
            <label class="form-check-label" for="service${service.ServiceID}">
                ${service.ServiceName} - $${parseFloat(service.Price).toFixed(2)}
                <small class="text-muted d-block">${service.Service_Description || ''}</small>
            </label>
        `;
        servicesDiv.appendChild(div);
    });
    
    // Add event listeners for price calculation
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateTotalPrice);
    });
}

async function loadBeauticians() {
    const result = await apiCall('/api/beauticians');
    
    if (result.success) {
        displayBeauticians(result.data);
    } else {
        showError('errorMessage', 'Failed to load beauticians');
    }
}

function displayBeauticians(beauticians) {
    const select = document.getElementById('beauticianSelect');
    
    beauticians.forEach(beautician => {
        const option = document.createElement('option');
        option.value = beautician.BeauticianID;
        option.textContent = `${beautician.First_Name} ${beautician.Last_Name}`;
        select.appendChild(option);
    });
}

function updateTotalPrice() {
    let total = 0;
    document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
        total += parseFloat(checkbox.dataset.price);
    });
    document.getElementById('totalPrice').textContent = total.toFixed(2);
}

// Handle form submission
document.getElementById('bookingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    hideError('errorMessage');
    hideError('successMessage');
    
    // Get selected services
    const selectedServiceIds = [];
    document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
        selectedServiceIds.push(parseInt(checkbox.value));
    });
    
    if (selectedServiceIds.length === 0) {
        showError('errorMessage', 'Please select at least one service.');
        return;
    }
    
    const appointmentData = {
        date: document.getElementById('appointmentDate').value,
        time: document.getElementById('appointmentTime').value,
        service_ids: selectedServiceIds,
        beautician_id: document.getElementById('beauticianSelect').value || null
    };
    
    // Disable submit button to prevent double-click
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Booking...';
    
    const result = await apiCall('/api/appointments', 'POST', appointmentData);
    
    if (result.success) {
        showSuccess('successMessage', 'Appointment booked successfully! Redirecting...');
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 2000);
    } else {
        showError('errorMessage', result.data.error || 'Failed to book appointment');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Book Appointment';
    }
});