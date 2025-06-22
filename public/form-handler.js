
// Form handling functionality
let isScheduleMode = false;

// Toggle schedule mode
function toggleScheduleMode() {
    isScheduleMode = document.getElementById('schedule-mode').checked;
    const scheduleFields = document.getElementById('schedule-fields');
    const serviceField = document.getElementById('service');
    const preferredDateField = document.getElementById('preferred_date');
    const preferredTimeField = document.getElementById('preferred_time');
    
    if (isScheduleMode) {
        scheduleFields.style.display = 'block';
        serviceField.required = true;
        preferredDateField.required = true;
        preferredTimeField.required = true;
        
        // Add event listeners for dynamic updates
        serviceField.addEventListener('change', window.updateTimeSlotAvailability);
        preferredDateField.addEventListener('change', window.updateTimeSlotAvailability);
    } else {
        scheduleFields.style.display = 'none';
        serviceField.required = false;
        preferredDateField.required = false;
        preferredTimeField.required = false;
        
        // Remove event listeners
        serviceField.removeEventListener('change', window.updateTimeSlotAvailability);
        preferredDateField.removeEventListener('change', window.updateTimeSlotAvailability);
    }
    
    window.updateTranslations();
}

// Handle form submission
async function handleFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = {
        first_name: formData.get('firstName'),
        last_name: formData.get('lastName'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        message: formData.get('message'),
        language: window.currentLanguage,
        status: isScheduleMode ? 'pending' : 'new'
    };

    try {
        if (isScheduleMode) {
            // Add schedule-specific fields
            data.service = formData.get('service');
            data.preferred_date = formData.get('preferred_date');
            data.preferred_time = formData.get('preferred_time');

            // Check for conflicts before submitting
            const conflict = await window.checkAppointmentConflicts(
                data.preferred_date, 
                data.preferred_time, 
                data.service
            );

            if (conflict.hasConflict) {
                alert(conflict.message);
                return;
            }

            const { error } = await window.supabaseClient
                .from('oregon_tires_appointments')
                .insert(data);

            if (error) throw error;
            
            alert(window.currentLanguage === 'english' ? 'Appointment scheduled successfully!' : '¡Cita programada exitosamente!');
        } else {
            // Contact message
            const { error } = await window.supabaseClient
                .from('oregon_tires_contact_messages')
                .insert(data);

            if (error) throw error;
            
            alert(window.currentLanguage === 'english' ? 'Message sent successfully!' : '¡Mensaje enviado exitosamente!');
        }

        // Reset form
        event.target.reset();
        document.getElementById('schedule-mode').checked = false;
        toggleScheduleMode();
    } catch (error) {
        console.error('Form submission error:', error);
        alert(window.currentLanguage === 'english' ? 'Error sending message. Please try again.' : 'Error al enviar mensaje. Por favor intente de nuevo.');
    }
}

// Open contact form
function openContactForm() {
    isScheduleMode = false;
    document.getElementById('schedule-mode').checked = false;
    toggleScheduleMode();
    scrollToSection('contact');
}

// Open schedule form
function openScheduleForm() {
    isScheduleMode = true;
    document.getElementById('schedule-mode').checked = true;
    toggleScheduleMode();
    scrollToSection('contact');
}

// Export for global use
window.isScheduleMode = isScheduleMode;
window.toggleScheduleMode = toggleScheduleMode;
window.handleFormSubmit = handleFormSubmit;
window.openContactForm = openContactForm;
window.openScheduleForm = openScheduleForm;
