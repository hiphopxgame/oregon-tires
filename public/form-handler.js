
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
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        preferredDateField.min = today;
        
        console.log('Schedule mode enabled');
    } else {
        scheduleFields.style.display = 'none';
        serviceField.required = false;
        preferredDateField.required = false;
        preferredTimeField.required = false;
        
        // Remove event listeners
        serviceField.removeEventListener('change', window.updateTimeSlotAvailability);
        preferredDateField.removeEventListener('change', window.updateTimeSlotAvailability);
        
        console.log('Schedule mode disabled');
    }
    
    if (window.updateTranslations) {
        window.updateTranslations();
    }
}

// Handle form submission
async function handleFormSubmit(event) {
    event.preventDefault();
    console.log('Form submission started');
    
    const formData = new FormData(event.target);
    const data = {
        first_name: formData.get('firstName'),
        last_name: formData.get('lastName'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        message: formData.get('message'),
        language: window.currentLanguage || 'english',
        status: isScheduleMode ? 'pending' : 'new'
    };

    try {
        if (isScheduleMode) {
            // Add schedule-specific fields
            data.service = formData.get('service');
            data.preferred_date = formData.get('preferred_date');
            data.preferred_time = formData.get('preferred_time');

            console.log('Checking for conflicts before submitting appointment:', data);

            // Check for conflicts before submitting
            const conflict = await window.checkAppointmentConflicts(
                data.preferred_date, 
                data.preferred_time, 
                data.service
            );

            if (conflict.hasConflict) {
                alert(window.currentLanguage === 'english' ? 
                    `Cannot book appointment: ${conflict.message}` :
                    `No se puede reservar la cita: ${conflict.message}`
                );
                console.log('Appointment blocked due to conflict:', conflict.message);
                return;
            }

            console.log('No conflicts found, submitting appointment');

            const { error } = await window.supabaseClient
                .from('oregon_tires_appointments')
                .insert(data);

            if (error) throw error;
            
            alert(window.currentLanguage === 'english' ? 
                'Appointment scheduled successfully!' : 
                '¡Cita programada exitosamente!'
            );
            console.log('Appointment scheduled successfully');
        } else {
            // Contact message
            console.log('Submitting contact message');
            const { error } = await window.supabaseClient
                .from('oregon_tires_contact_messages')
                .insert(data);

            if (error) throw error;
            
            alert(window.currentLanguage === 'english' ? 
                'Message sent successfully!' : 
                '¡Mensaje enviado exitosamente!'
            );
            console.log('Contact message sent successfully');
        }

        // Reset form
        event.target.reset();
        document.getElementById('schedule-mode').checked = false;
        toggleScheduleMode();
        
        // Refresh appointment preview if it exists
        if (window.updatePreviewTimeSlots) {
            window.updatePreviewTimeSlots();
        }
        
    } catch (error) {
        console.error('Form submission error:', error);
        alert(window.currentLanguage === 'english' ? 
            'Error sending message. Please try again.' : 
            'Error al enviar mensaje. Por favor intente de nuevo.'
        );
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
