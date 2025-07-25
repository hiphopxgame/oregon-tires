
// Form handling functionality - Contact messages only
// All appointment booking has been moved to /book-appointment

// Handle form submission for contact messages only
async function handleFormSubmit(event) {
    event.preventDefault();
    console.log('Contact form submission started');
    
    const formData = new FormData(event.target);
    const data = {
        first_name: formData.get('firstName'),
        last_name: formData.get('lastName'),
        phone: formData.get('phone'),
        email: formData.get('email'),
        message: formData.get('message'),
        language: window.currentLanguage || 'english',
        status: 'new'
    };

    try {
        console.log('Submitting contact message');
        const { error } = await window.supabaseClient
            .from('oretir_contact_messages')
            .insert(data);

        if (error) throw error;
        
        alert(window.currentLanguage === 'english' ? 
            'Message sent successfully!' : 
            '¡Mensaje enviado exitosamente!'
        );
        console.log('Contact message sent successfully');

        // Reset form
        event.target.reset();
        
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
    scrollToSection('contact');
}

// Redirect to booking page for all appointment scheduling
function openScheduleForm() {
    window.location.href = '/book-appointment';
}

// Export for global use
window.handleFormSubmit = handleFormSubmit;
window.openContactForm = openContactForm;
window.openScheduleForm = openScheduleForm;
