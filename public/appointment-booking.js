
// Appointment booking functionality

// Book appointment slot - pre-fill the contact form
function bookAppointmentSlot(time) {
    const serviceSelect = document.getElementById('preview-service');
    const dateInput = document.getElementById('preview-date');

    if (!serviceSelect || !dateInput) return;

    // Pre-fill the contact form
    document.getElementById('schedule-mode').checked = true;
    window.toggleScheduleMode();

    // Fill in the service and date/time
    document.getElementById('service').value = serviceSelect.value;
    document.getElementById('preferred_date').value = dateInput.value;
    document.getElementById('preferred_time').value = time;

    // Scroll to contact form
    window.scrollToSection('contact');

    // Show confirmation
    const timeDisplay = window.formatTimeDisplay(time);
    const serviceName = serviceSelect.options[serviceSelect.selectedIndex].text;

    alert(window.currentLanguage === 'english' ? 
        `Selected ${timeDisplay} for ${serviceName}. Please fill out the contact form below to complete your booking.` :
        `Seleccionado ${timeDisplay} para ${serviceName}. Por favor complete el formulario de contacto a continuación para completar su reserva.`
    );
}

// Setup event listeners for appointment preview
function setupPreviewEventListeners() {
    const serviceSelect = document.getElementById('preview-service');
    const dateInput = document.getElementById('preview-date');

    if (serviceSelect && dateInput) {
        serviceSelect.addEventListener('change', window.updatePreviewTimeSlots);
        dateInput.addEventListener('change', window.updatePreviewTimeSlots);

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }
}

// Export functions for global use
window.bookAppointmentSlot = bookAppointmentSlot;
window.setupPreviewEventListeners = setupPreviewEventListeners;
