
// Appointment preview functionality

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
            const conflict = await window.checkAppointmentConflicts(date, time, service);
            
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
    alert(window.currentLanguage === 'english' ? 
        `Selected ${time} for ${serviceSelect.options[serviceSelect.selectedIndex].text}. Please fill out the contact form below to complete your booking.` :
        `Seleccionado ${time} para ${serviceSelect.options[serviceSelect.selectedIndex].text}. Por favor complete el formulario de contacto a continuación para completar su reserva.`
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

// Export functions for global use
window.addAppointmentPreview = addAppointmentPreview;
window.bookAppointmentSlot = bookAppointmentSlot;
window.updatePreviewTimeSlots = updatePreviewTimeSlots;
