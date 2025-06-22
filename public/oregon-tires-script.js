// Initialize Supabase
const { createClient } = supabase;
const supabaseClient = createClient(
    'https://vtknmauyvmuaryttnenx.supabase.co',
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ0a25tYXV5dm11YXJ5dHRuZW54Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MDE1MDgsImV4cCI6MjA2NTA3NzUwOH0._bOyuxj1nRBw3U7Q3qCsDubBNg_EM-VLhQB0y5p9okY'
);

// Language state
let currentLanguage = 'english';
let isScheduleMode = false;

// Service durations (in hours)
const serviceDurations = {
    'tire-installation': 1.5,
    'tire-repair': 1.5,
    'wheel-alignment': 1.5,
    'brake-service': 2.5,
    'brake-repair': 2.5,
    'oil-change': 3.5,
    'general-maintenance': 3.5,
    'diagnostic': 3.5
};

// Convert time string to minutes from start of day
function timeToMinutes(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
}

// Check for appointment conflicts
async function checkAppointmentConflicts(date, time, service) {
    try {
        console.log(`Checking conflicts for ${date} at ${time} for ${service}`);
        
        // Fetch appointments for the selected date
        const { data: appointments, error } = await supabaseClient
            .from('oregon_tires_appointments')
            .select('*')
            .eq('preferred_date', date)
            .neq('status', 'cancelled');

        if (error) {
            console.error('Error fetching appointments:', error);
            throw error;
        }

        console.log(`Found ${appointments?.length || 0} existing appointments for ${date}`);

        const serviceDuration = serviceDurations[service] || 1.5;
        const slotStartMinutes = timeToMinutes(time);
        const slotEndMinutes = slotStartMinutes + (serviceDuration * 60);
        const closingTime = 19 * 60; // 7 PM in minutes

        // Check if service extends beyond business hours
        if (slotEndMinutes > closingTime) {
            const overtimeHours = Math.round((slotEndMinutes - closingTime) / 60 * 10) / 10;
            console.log(`Service extends ${overtimeHours} hours beyond closing time`);
            return {
                hasConflict: true,
                message: `This service would extend ${overtimeHours} hours beyond our closing time (7 PM). Please choose an earlier time.`
            };
        }

        // Count overlapping appointments
        let overlappingCount = 0;
        const conflictingAppointments = [];

        appointments.forEach(apt => {
            const aptStartMinutes = timeToMinutes(apt.preferred_time.substring(0, 5));
            const aptDuration = serviceDurations[apt.service] || 1.5;
            const aptEndMinutes = aptStartMinutes + (aptDuration * 60);

            // Check if appointments overlap
            if (slotStartMinutes < aptEndMinutes && slotEndMinutes > aptStartMinutes) {
                overlappingCount++;
                conflictingAppointments.push(`${apt.first_name} ${apt.last_name} (${apt.service})`);
                console.log(`Conflict found with ${apt.first_name} ${apt.last_name} (${apt.service})`);
            }
        });

        console.log(`Found ${overlappingCount} overlapping appointments`);

        // Maximum 2 simultaneous appointments allowed
        if (overlappingCount >= 2) {
            return {
                hasConflict: true,
                message: `This time slot is fully booked (${overlappingCount} appointments already scheduled). Maximum 2 appointments allowed simultaneously. Please choose a different time.`
            };
        }

        if (overlappingCount === 1) {
            return {
                hasConflict: false,
                message: `Limited availability: 1 appointment slot remaining for this time.`,
                isLimited: true
            };
        }

        return {
            hasConflict: false,
            message: 'Time slot available'
        };

    } catch (error) {
        console.error('Error checking conflicts:', error);
        return {
            hasConflict: false,
            message: 'Unable to verify availability'
        };
    }
}

// Add appointment preview section to the page
function addAppointmentPreview() {
    const contactSection = document.getElementById('contact');
    if (!contactSection) return;

    // Create preview section
    const previewSection = document.createElement('section');
    previewSection.id = 'appointment-preview';
    previewSection.className = 'py-16 bg-gray-50';
    previewSection.innerHTML = `
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold primary-color mb-4">Check Appointment Availability</h2>
                <p class="text-xl text-gray-600">See real-time availability for your preferred service and time</p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <!-- Service and Date Selection -->
                <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Service</label>
                            <select id="preview-service" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                                <option value="">Select a service</option>
                                <option value="tire-installation">Tire Installation (1.5h)</option>
                                <option value="tire-repair">Tire Repair (1.5h)</option>
                                <option value="wheel-alignment">Wheel Alignment (1.5h)</option>
                                <option value="brake-service">Brake Service (2.5h)</option>
                                <option value="brake-repair">Brake Repair (2.5h)</option>
                                <option value="oil-change">Oil Change (3.5h)</option>
                                <option value="general-maintenance">General Maintenance (3.5h)</option>
                                <option value="diagnostic">Diagnostic Service (3.5h)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Date</label>
                            <input type="date" id="preview-date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Time Slots Grid -->
                <div id="time-slots-container" class="bg-white p-6 rounded-lg shadow-lg" style="display: none;">
                    <h3 class="text-xl font-semibold mb-4">Available Time Slots</h3>
                    <div id="time-slots-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
                        <!-- Time slots will be populated here -->
                    </div>
                    
                    <!-- Legend -->
                    <div class="flex flex-wrap gap-4 justify-center text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                            <span>Available (2 slots)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                            <span>Limited (1 slot left)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                            <span>Unavailable</span>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 border-2 border-blue-200 p-4 rounded-lg mt-6">
                    <h3 class="font-semibold mb-2 text-blue-800">Booking Information:</h3>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Maximum 2 appointments can be scheduled simultaneously</li>
                        <li>• All services must be completed by 7 PM closing time</li>
                        <li>• We are closed on Sundays</li>
                        <li>• Click on an available time slot to book your appointment</li>
                    </ul>
                </div>
            </div>
        </div>
    `;

    // Insert before contact section
    contactSection.parentNode.insertBefore(previewSection, contactSection);

    // Add event listeners
    setupPreviewEventListeners();
}

// Setup event listeners for appointment preview
function setupPreviewEventListeners() {
    const serviceSelect = document.getElementById('preview-service');
    const dateInput = document.getElementById('preview-date');

    if (serviceSelect && dateInput) {
        serviceSelect.addEventListener('change', updatePreviewTimeSlots);
        dateInput.addEventListener('change', updatePreviewTimeSlots);

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }
}

// Update preview time slots
async function updatePreviewTimeSlots() {
    const serviceSelect = document.getElementById('preview-service');
    const dateInput = document.getElementById('preview-date');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const timeSlotsGrid = document.getElementById('time-slots-grid');

    if (!serviceSelect || !dateInput || !timeSlotsContainer || !timeSlotsGrid) return;

    const service = serviceSelect.value;
    const date = dateInput.value;

    if (!service || !date) {
        timeSlotsContainer.style.display = 'none';
        return;
    }

    // Check if selected date is Sunday
    const selectedDate = new Date(date);
    if (selectedDate.getDay() === 0) {
        timeSlotsGrid.innerHTML = '<div class="col-span-full text-center text-red-600 py-8">We are closed on Sundays</div>';
        timeSlotsContainer.style.display = 'block';
        return;
    }

    // Show loading
    timeSlotsGrid.innerHTML = '<div class="col-span-full text-center py-8">Loading availability...</div>';
    timeSlotsContainer.style.display = 'block';

    try {
        // Generate time slots
        const timeSlots = [];
        for (let hour = 7; hour < 19; hour++) {
            timeSlots.push(`${hour.toString().padStart(2, '0')}:00`);
            timeSlots.push(`${hour.toString().padStart(2, '0')}:30`);
        }

        // Check availability for each slot
        const slotElements = [];
        for (const time of timeSlots) {
            const conflict = await checkAppointmentConflicts(date, time, service);
            
            const hour = parseInt(time.split(':')[0]);
            const minute = time.split(':')[1];
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
            const timeDisplay = `${displayHour}:${minute} ${ampm}`;

            let buttonClass = 'w-full h-16 flex flex-col items-center justify-center text-xs rounded-lg border-2 transition-colors cursor-pointer';
            let statusText = '';
            let clickHandler = '';

            if (conflict.hasConflict) {
                buttonClass += ' bg-red-100 border-red-300 text-red-800 cursor-not-allowed';
                statusText = 'Unavailable';
            } else if (conflict.isLimited) {
                buttonClass += ' bg-yellow-100 border-yellow-300 text-yellow-800 hover:bg-yellow-200';
                statusText = 'Limited';
                clickHandler = `onclick="bookAppointmentSlot('${time}')"`;
            } else {
                buttonClass += ' bg-green-100 border-green-300 text-green-800 hover:bg-green-200';
                statusText = 'Available';
                clickHandler = `onclick="bookAppointmentSlot('${time}')"`;
            }

            slotElements.push(`
                <button class="${buttonClass}" ${clickHandler} ${conflict.hasConflict ? 'disabled' : ''}>
                    <div class="font-semibold">${timeDisplay}</div>
                    <div class="text-xs opacity-75">${statusText}</div>
                </button>
            `);
        }

        timeSlotsGrid.innerHTML = slotElements.join('');

    } catch (error) {
        console.error('Error updating time slots:', error);
        timeSlotsGrid.innerHTML = '<div class="col-span-full text-center text-red-600 py-8">Error loading availability. Please try again.</div>';
    }
}

// Book appointment slot
function bookAppointmentSlot(time) {
    const serviceSelect = document.getElementById('preview-service');
    const dateInput = document.getElementById('preview-date');

    if (!serviceSelect || !dateInput) return;

    // Pre-fill the contact form
    document.getElementById('schedule-mode').checked = true;
    toggleScheduleMode();

    // Fill in the service and date/time
    document.getElementById('service').value = serviceSelect.value;
    document.getElementById('preferred_date').value = dateInput.value;
    document.getElementById('preferred_time').value = time;

    // Scroll to contact form
    scrollToSection('contact');

    // Show confirmation
    alert(currentLanguage === 'english' ? 
        `Selected ${time} for ${serviceSelect.options[serviceSelect.selectedIndex].text}. Please fill out the contact form below to complete your booking.` :
        `Seleccionado ${time} para ${serviceSelect.options[serviceSelect.selectedIndex].text}. Por favor complete el formulario de contacto a continuación para completar su reserva.`
    );
}

// Update time slot availability when service or date changes
async function updateTimeSlotAvailability() {
    const serviceField = document.getElementById('service');
    const dateField = document.getElementById('preferred_date');
    const timeField = document.getElementById('preferred_time');
    
    if (!serviceField.value || !dateField.value) {
        return;
    }

    const service = serviceField.value;
    const date = dateField.value;
    
    // Clear existing options except the first one
    const firstOption = timeField.options[0];
    timeField.innerHTML = '';
    timeField.appendChild(firstOption);

    // Add time slots with availability info
    const timeSlots = [
        '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
        '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
        '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
        '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'
    ];

    for (const time of timeSlots) {
        const conflict = await checkAppointmentConflicts(date, time, service);
        const option = document.createElement('option');
        option.value = time;
        
        const hour = parseInt(time.split(':')[0]);
        const minute = time.split(':')[1];
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
        const timeDisplay = `${displayHour}:${minute} ${ampm}`;
        
        if (conflict.hasConflict) {
            option.textContent = `${timeDisplay} - Unavailable`;
            option.disabled = true;
            option.style.color = '#dc2626';
        } else if (conflict.isLimited) {
            option.textContent = `${timeDisplay} - Limited (1 slot left)`;
            option.style.color = '#d97706';
        } else {
            option.textContent = `${timeDisplay} - Available`;
            option.style.color = '#059669';
        }
        
        timeField.appendChild(option);
    }
}

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
        
        // Add event listeners for dynamic updates
        serviceField.addEventListener('change', updateTimeSlotAvailability);
        preferredDateField.addEventListener('change', updateTimeSlotAvailability);
    } else {
        scheduleFields.style.display = 'none';
        serviceField.required = false;
        preferredDateField.required = false;
        preferredTimeField.required = false;
        
        // Remove event listeners
        serviceField.removeEventListener('change', updateTimeSlotAvailability);
        preferredDateField.removeEventListener('change', updateTimeSlotAvailability);
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

            // Check for conflicts before submitting
            const conflict = await checkAppointmentConflicts(
                data.preferred_date, 
                data.preferred_time, 
                data.service
            );

            if (conflict.hasConflict) {
                alert(conflict.message);
                return;
            }

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
    
    // Add appointment preview section
    addAppointmentPreview();
});
