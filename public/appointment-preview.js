
// Appointment preview functionality

// Update preview time slots
async function updatePreviewTimeSlots() {
    const serviceSelect = document.getElementById('preview-service');
    const dateInput = document.getElementById('preview-date');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const availableTimesList = document.getElementById('available-times-list');

    if (!serviceSelect || !dateInput || !timeSlotsContainer || !availableTimesList) return;

    const service = serviceSelect.value;
    const date = dateInput.value;

    if (!service || !date) {
        timeSlotsContainer.style.display = 'none';
        return;
    }

    // Check if selected date is Sunday
    const selectedDate = new Date(date);
    if (selectedDate.getDay() === 0) {
        availableTimesList.innerHTML = '<div class="text-center text-red-600 py-8">We are closed on Sundays</div>';
        timeSlotsContainer.style.display = 'block';
        return;
    }

    // Show loading
    availableTimesList.innerHTML = '<div class="text-center py-8">Loading availability...</div>';
    timeSlotsContainer.style.display = 'block';

    try {
        // Generate time slots
        const timeSlots = [];
        for (let hour = 7; hour < 19; hour++) {
            timeSlots.push(`${hour.toString().padStart(2, '0')}:00`);
            timeSlots.push(`${hour.toString().padStart(2, '0')}:30`);
        }

        // Check availability for each slot and build lists
        const availableTimes = [];
        const limitedTimes = [];
        const unavailableTimes = [];

        for (const time of timeSlots) {
            const conflict = await window.checkAppointmentConflicts(date, time, service);
            
            const hour = parseInt(time.split(':')[0]);
            const minute = time.split(':')[1];
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
            const timeDisplay = `${displayHour}:${minute} ${ampm}`;

            const timeInfo = {
                time: time,
                display: timeDisplay,
                conflict: conflict
            };

            if (conflict.hasConflict) {
                unavailableTimes.push(timeInfo);
            } else if (conflict.isLimited) {
                limitedTimes.push(timeInfo);
            } else {
                availableTimes.push(timeInfo);
            }
        }

        // Build the display
        let html = '';

        // Available times section
        if (availableTimes.length > 0) {
            html += `
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-green-700 mb-3 flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        Available Times (${availableTimes.length} slots)
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        ${availableTimes.map(timeinfo => `
                            <button 
                                class="bg-green-100 border-2 border-green-300 text-green-800 px-4 py-3 rounded-lg font-medium hover:bg-green-200 transition-colors"
                                onclick="bookAppointmentSlot('${timeinfo.time}')"
                            >
                                ${timeinfo.display}
                            </button>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // Limited availability times section
        if (limitedTimes.length > 0) {
            html += `
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-yellow-700 mb-3 flex items-center">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                        Limited Availability (${limitedTimes.length} slots - 1 spot left each)
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        ${limitedTimes.map(timeinfo => `
                            <button 
                                class="bg-yellow-100 border-2 border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg font-medium hover:bg-yellow-200 transition-colors"
                                onclick="bookAppointmentSlot('${timeinfo.time}')"
                            >
                                ${timeinfo.display}
                            </button>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // Unavailable times section (collapsed by default)
        if (unavailableTimes.length > 0) {
            html += `
                <div class="mb-6">
                    <button 
                        class="text-lg font-semibold text-red-700 mb-3 flex items-center w-full text-left hover:text-red-800"
                        onclick="toggleUnavailableSection()"
                        id="unavailable-toggle"
                    >
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        Unavailable Times (${unavailableTimes.length} slots)
                        <svg class="w-4 h-4 ml-2 transform transition-transform" id="unavailable-arrow">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 hidden" id="unavailable-times">
                        ${unavailableTimes.map(timeinfo => `
                            <div 
                                class="bg-red-100 border-2 border-red-300 text-red-800 px-4 py-3 rounded-lg font-medium cursor-not-allowed opacity-75"
                                title="${timeinfo.conflict.message}"
                            >
                                ${timeinfo.display}
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // Summary
        const totalAvailable = availableTimes.length + limitedTimes.length;
        if (totalAvailable === 0) {
            html = `
                <div class="text-center py-8">
                    <div class="text-red-600 text-lg font-semibold mb-2">No Available Times</div>
                    <p class="text-gray-600">All time slots are fully booked for this date and service. Please try a different date.</p>
                </div>
            `;
        } else {
            html = `
                <div class="bg-blue-50 border-2 border-blue-200 p-4 rounded-lg mb-6">
                    <h3 class="font-semibold text-blue-800 mb-2">Available Times Summary:</h3>
                    <p class="text-blue-700 text-sm">
                        Found <strong>${totalAvailable} available time slots</strong> for ${serviceSelect.options[serviceSelect.selectedIndex].text} on ${new Date(date).toLocaleDateString()}
                    </p>
                </div>
            ` + html;
        }

        availableTimesList.innerHTML = html;

    } catch (error) {
        console.error('Error updating time slots:', error);
        availableTimesList.innerHTML = '<div class="text-center text-red-600 py-8">Error loading availability. Please try again.</div>';
    }
}

// Toggle unavailable times section
function toggleUnavailableSection() {
    const unavailableTimes = document.getElementById('unavailable-times');
    const arrow = document.getElementById('unavailable-arrow');
    
    if (unavailableTimes && arrow) {
        if (unavailableTimes.classList.contains('hidden')) {
            unavailableTimes.classList.remove('hidden');
            arrow.classList.add('rotate-90');
        } else {
            unavailableTimes.classList.add('hidden');
            arrow.classList.remove('rotate-90');
        }
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
    const hour = parseInt(time.split(':')[0]);
    const minute = time.split(':')[1];
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    const timeDisplay = `${displayHour}:${minute} ${ampm}`;

    alert(window.currentLanguage === 'english' ? 
        `Selected ${timeDisplay} for ${serviceSelect.options[serviceSelect.selectedIndex].text}. Please fill out the contact form below to complete your booking.` :
        `Seleccionado ${timeDisplay} para ${serviceSelect.options[serviceSelect.selectedIndex].text}. Por favor complete el formulario de contacto a continuación para completar su reserva.`
    );
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

                <!-- Available Times List -->
                <div id="time-slots-container" class="bg-white p-6 rounded-lg shadow-lg" style="display: none;">
                    <h3 class="text-xl font-semibold mb-4">Available Time Slots</h3>
                    <div id="available-times-list">
                        <!-- Available times will be populated here -->
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

// Export functions for global use
window.addAppointmentPreview = addAppointmentPreview;
window.bookAppointmentSlot = bookAppointmentSlot;
window.updatePreviewTimeSlots = updatePreviewTimeSlots;
window.toggleUnavailableSection = toggleUnavailableSection;
