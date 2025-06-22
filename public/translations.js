
// Language state
let currentLanguage = 'english';

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
        'contact-title': window.isScheduleMode ? t.scheduleService : t.contact,
        'contact-subtitle': t.contactSubtitle,
        'contact-info': t.contactInfo,
        'phone-label': t.phone,
        'business-hours': t.businessHours,
        'hours-text': t.monSat,
        'hours-mon-sat': t.monSat,
        'hours-sunday': t.sunday,
        'form-title': window.isScheduleMode ? t.scheduleService : t.contact,
        'toggle-label': window.isScheduleMode ? t.toggleToContact : t.toggleToSchedule,
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
        'submit-text': window.isScheduleMode ? t.scheduleAppointment : t.sendMessage,
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

// Export for global use
window.currentLanguage = currentLanguage;
window.toggleLanguage = toggleLanguage;
window.updateTranslations = updateTranslations;
window.translations = translations;
