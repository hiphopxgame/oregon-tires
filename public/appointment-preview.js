
// Main appointment preview functionality - coordinates all modules

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
        availableTimesList.innerHTML = window.buildClosedSundayHTML();
        timeSlotsContainer.style.display = 'block';
        return;
    }

    // Show loading
    availableTimesList.innerHTML = '<div class="text-center py-8">Loading availability...</div>';
    timeSlotsContainer.style.display = 'block';

    try {
        // Get availability data for all time slots
        const availability = await window.checkAllTimeSlotAvailability(date, service);

        // Build the display HTML
        let html = '';

        if (availability.total === 0) {
            html = window.buildNoAvailabilityHTML();
        } else {
            // Add summary
            html += window.buildSummaryHTML(serviceSelect, dateInput, availability.total);
            
            // Add available times
            html += window.buildAvailableTimesHTML(availability.available);
            
            // Add limited times
            html += window.buildLimitedTimesHTML(availability.limited);
            
            // Add unavailable times
            html += window.buildUnavailableTimesHTML(availability.unavailable);
        }

        availableTimesList.innerHTML = html;

    } catch (error) {
        console.error('Error updating time slots:', error);
        availableTimesList.innerHTML = '<div class="text-center text-red-600 py-8">Error loading availability. Please try again.</div>';
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
    window.setupPreviewEventListeners();
}

// Export functions for global use
window.addAppointmentPreview = addAppointmentPreview;
window.updatePreviewTimeSlots = updatePreviewTimeSlots;
