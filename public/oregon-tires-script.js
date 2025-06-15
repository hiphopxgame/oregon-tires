
// Initialize Supabase
const { createClient } = supabase;
const supabaseClient = createClient(
    'https://vtknmauyvmuaryttnenx.supabase.co',
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ0a25tYXV5dm11YXJ5dHRuZW54Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MDE1MDgsImV4cCI6MjA2NTA3NzUwOH0._bOyuxj1nRBw3U7Q3qCsDubBNg_EM-VLhQB0y5p9okY'
);

// Language state
let currentLanguage = 'english';
let isScheduleMode = false;

// Customer reviews data
const customerReviews = [
    {
        name: "Maria Rodriguez",
        rating: 5,
        date: "2 weeks ago",
        review: "Excellent service! They installed my new tires quickly and the price was very fair. The staff speaks Spanish which made communication easy. Highly recommend!"
    },
    {
        name: "James Thompson",
        rating: 5,
        date: "1 month ago", 
        review: "Been coming here for years. They always do quality work and are honest about what needs to be done. Fixed my brake issue same day."
    },
    {
        name: "Sarah Chen",
        rating: 5,
        date: "3 weeks ago",
        review: "Fast and professional service. Had a flat tire emergency and they got me back on the road in 30 minutes. Great customer service!"
    },
    {
        name: "Roberto Gonzalez",
        rating: 5,
        date: "2 months ago",
        review: "Servicio excelente! Me ayudaron con la alineación de mis llantas. Personal muy amable y precios justos. Los recomiendo."
    },
    {
        name: "Jennifer Smith",
        rating: 4,
        date: "1 week ago",
        review: "Good experience overall. They diagnosed my car trouble quickly and fixed it at a reasonable price. Only complaint was the wait time."
    },
    {
        name: "Miguel Santos",
        rating: 5,
        date: "4 weeks ago",
        review: "Very helpful staff. They explained everything clearly in Spanish and English. Quality tire installation and fair pricing."
    }
];

// Function to render star ratings
function renderStars(rating) {
    let starsHTML = '';
    for (let i = 0; i < 5; i++) {
        if (i < rating) {
            starsHTML += '<i data-lucide="star" class="h-4 w-4 fill-current text-yellow-400"></i>';
        } else {
            starsHTML += '<i data-lucide="star" class="h-4 w-4 text-gray-300"></i>';
        }
    }
    return starsHTML;
}

// Function to populate reviews
function populateReviews() {
    const reviewsContainer = document.getElementById('reviews-container');
    // Randomly select 3 reviews
    const shuffled = [...customerReviews].sort(() => 0.5 - Math.random());
    const selectedReviews = shuffled.slice(0, 3);

    reviewsContainer.innerHTML = selectedReviews.map(review => `
        <div class="bg-gray-50 hover:shadow-lg transition-shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex">
                        ${renderStars(review.rating)}
                    </div>
                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">Verified</span>
                </div>
                <h3 class="text-lg font-semibold mb-1">${review.name}</h3>
                <p class="text-sm text-gray-500 mb-4">${review.date}</p>
                <p class="text-gray-600">${review.review}</p>
            </div>
        </div>
    `).join('');

    // Re-initialize Lucide icons for the new content
    lucide.createIcons();
}

// Translations
const translations = {
    english: {
        title: "Oregon Tires Auto Care",
        subtitle: "Professional Tire & Auto Services",
        heroTitle: "Your Trusted Auto Care Experts in Portland",
        heroSubtitle: "Professional tire installation, repair, and complete automotive services. We speak Spanish and English!",
        contactButton: "Contact Us",
        appointmentButton: "Book Appointment",
        scheduleService: "Schedule Service",
        services: "Services",
        about: "About",
        contact: "Contact",
        contactInfo: "Contact Information",
        phone: "Phone",
        businessHours: "Business Hours",
        monSat: "Mon-Sat 7AM-7PM",
        sunday: "Sunday: Closed",
        firstName: "First Name",
        lastName: "Last Name",
        email: "Email",
        message: "Message",
        serviceNeeded: "Service Needed",
        selectService: "Select a service",
        preferredDate: "Preferred Date",
        preferredTime: "Preferred Time",
        selectTime: "Select a time",
        scheduleAppointment: "Schedule Appointment",
        sendMessage: "Send Message",
        toggleToContact: "Switch to Contact",
        toggleToSchedule: "Switch to Schedule",
        contactSubtitle: "Get in touch with us for all your automotive needs",
        visitLocation: "Visit Our Location",
        aboutSubtitle: "Serving Portland with honest, reliable automotive services since 2008",
        servicesSubtitle: "Comprehensive automotive services you can trust",
        tireServices: "Tire Services",
        autoMaintenance: "Auto Maintenance",
        emergencyService: "Emergency Services",
        tireInstallation: "Tire Installation",
        tireRepair: "Tire Repair",
        wheelAlignment: "Wheel Alignment",
        brakeService: "Brake Service",
        oilChange: "Oil Change",
        allRightsReserved: "All rights reserved",
        customerReviews: "Customer Reviews",
        customerReviewsSubtitle: "What our customers say about our service"
    },
    spanish: {
        title: "Oregon Tires Auto Care",
        subtitle: "Servicios Profesionales de Llantas y Autos",
        heroTitle: "Sus Expertos de Confianza en Cuidado Automotriz en Portland",
        heroSubtitle: "Instalación profesional de llantas, reparación y servicios automotrices completos. ¡Hablamos español e inglés!",
        contactButton: "Contáctanos",
        appointmentButton: "Reservar Cita",
        scheduleService: "Programar Servicio",
        services: "Servicios",
        about: "Acerca de",
        contact: "Contacto",
        contactInfo: "Información de Contacto",
        phone: "Teléfono",
        businessHours: "Horarios de Atención",
        monSat: "Lun-Sáb 7AM-7PM",
        sunday: "Domingo: Cerrado",
        firstName: "Nombre",
        lastName: "Apellido",
        email: "Correo Electrónico",
        message: "Mensaje",
        serviceNeeded: "Servicio Necesario",
        selectService: "Selecciona un servicio",
        preferredDate: "Fecha Preferida",
        preferredTime: "Hora Preferida",
        selectTime: "Selecciona una hora",
        scheduleAppointment: "Programar Cita",
        sendMessage: "Enviar Mensaje",
        toggleToContact: "Cambiar a Contacto",
        toggleToSchedule: "Cambiar a Programar",
        contactSubtitle: "Ponte en contacto con nosotros para todas tus necesidades automotrices",
        visitLocation: "Visita Nuestra Ubicación",
        aboutSubtitle: "Sirviendo a Portland con servicios automotrices honestos y confiables desde 2008",
        servicesSubtitle: "Servicios automotrices integrales en los que puedes confiar",
        tireServices: "Servicios de Llantas",
        autoMaintenance: "Mantenimiento Automotriz",
        emergencyService: "Servicios de Emergencia",
        tireInstallation: "Instalación de Llantas",
        tireRepair: "Reparación de Llantas",
        wheelAlignment: "Alineación de Ruedas",
        brakeService: "Servicio de Frenos",
        oilChange: "Cambio de Aceite",
        allRightsReserved: "Todos los derechos reservados",
        customerReviews: "Reseñas de Clientes",
        customerReviewsSubtitle: "Lo que dicen nuestros clientes sobre nuestro servicio"
    }
};

// Toggle language function
function toggleLanguage() {
    currentLanguage = currentLanguage === 'english' ? 'spanish' : 'english';
    updateTranslations();
}

// Update translations
function updateTranslations() {
    const t = translations[currentLanguage];
    
    // Update all text elements
    const elements = {
        'site-title': t.title,
        'site-subtitle': t.subtitle,
        'hero-title': t.heroTitle,
        'hero-subtitle': t.heroSubtitle,
        'contact-btn': t.contactButton,
        'appointment-btn': t.appointmentButton,
        'schedule-btn': t.scheduleService,
        'nav-services': t.services,
        'nav-about': t.about,
        'nav-contact': t.contact,
        'services-title': t.services,
        'services-subtitle': t.servicesSubtitle,
        'tire-services': t.tireServices,
        'auto-maintenance': t.autoMaintenance,
        'emergency-service': t.emergencyService,
        'about-subtitle': t.aboutSubtitle,
        'contact-title': isScheduleMode ? t.scheduleService : t.contact,
        'contact-subtitle': t.contactSubtitle,
        'contact-info': t.contactInfo,
        'phone-label': t.phone,
        'business-hours': t.businessHours,
        'hours-text': t.monSat,
        'hours-mon-sat': t.monSat,
        'hours-sunday': t.sunday,
        'form-title': isScheduleMode ? t.scheduleService : t.contact,
        'toggle-label': isScheduleMode ? t.toggleToContact : t.toggleToSchedule,
        'first-name-label': t.firstName + ' *',
        'last-name-label': t.lastName + ' *',
        'phone-label-form': t.phone + ' *',
        'email-label': t.email + ' *',
        'message-label': t.message + ' *',
        'service-label': t.serviceNeeded + ' *',
        'select-service': t.selectService,
        'preferred-date-label': t.preferredDate + ' *',
        'preferred-time-label': t.preferredTime + ' *',
        'select-time': t.selectTime,
        'submit-text': isScheduleMode ? t.scheduleAppointment : t.sendMessage,
        'visit-location': t.visitLocation,
        'footer-contact-info': t.contactInfo,
        'footer-hours': t.monSat,
        'footer-services': t.services,
        'footer-tire-installation': t.tireInstallation,
        'footer-tire-repair': t.tireRepair,
        'footer-wheel-alignment': t.wheelAlignment,
        'footer-brake-service': t.brakeService,
        'footer-oil-change': t.oilChange,
        'all-rights': t.allRightsReserved,
        'testimonials-title': t.customerReviews,
        'testimonials-subtitle': t.customerReviewsSubtitle
    };

    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
}

// Scroll to section function
function scrollToSection(id) {
    const element = document.getElementById(id);
    if (element) {
        const headerHeight = 120;
        const elementPosition = element.offsetTop - headerHeight;
        window.scrollTo({ 
            top: elementPosition, 
            behavior: 'smooth' 
        });
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
    } else {
        scheduleFields.style.display = 'none';
        serviceField.required = false;
        preferredDateField.required = false;
        preferredTimeField.required = false;
    }
    
    updateTranslations();
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
        language: currentLanguage,
        status: isScheduleMode ? 'pending' : 'new'
    };

    try {
        if (isScheduleMode) {
            // Add schedule-specific fields
            data.service = formData.get('service');
            data.preferred_date = formData.get('preferred_date');
            data.preferred_time = formData.get('preferred_time');

            const { error } = await supabaseClient
                .from('oregon_tires_appointments')
                .insert(data);

            if (error) throw error;
            
            alert(currentLanguage === 'english' ? 'Appointment scheduled successfully!' : '¡Cita programada exitosamente!');
        } else {
            // Contact message
            const { error } = await supabaseClient
                .from('oregon_tires_contact_messages')
                .insert(data);

            if (error) throw error;
            
            alert(currentLanguage === 'english' ? 'Message sent successfully!' : '¡Mensaje enviado exitosamente!');
        }

        // Reset form
        event.target.reset();
        document.getElementById('schedule-mode').checked = false;
        toggleScheduleMode();
    } catch (error) {
        console.error('Form submission error:', error);
        alert(currentLanguage === 'english' ? 'Error sending message. Please try again.' : 'Error al enviar mensaje. Por favor intente de nuevo.');
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    populateReviews();
    updateTranslations();
});
