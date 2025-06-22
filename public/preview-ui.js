
// Preview UI display functionality

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

// Build HTML for available time slots
function buildAvailableTimesHTML(availableTimes) {
    if (availableTimes.length === 0) return '';
    
    return `
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

// Build HTML for limited availability time slots
function buildLimitedTimesHTML(limitedTimes) {
    if (limitedTimes.length === 0) return '';
    
    return `
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

// Build HTML for unavailable time slots
function buildUnavailableTimesHTML(unavailableTimes) {
    if (unavailableTimes.length === 0) return '';
    
    return `
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

// Build summary HTML
function buildSummaryHTML(serviceSelect, dateInput, totalAvailable) {
    const serviceName = serviceSelect.options[serviceSelect.selectedIndex].text;
    const formattedDate = new Date(dateInput.value).toLocaleDateString();
    
    return `
        <div class="bg-blue-50 border-2 border-blue-200 p-4 rounded-lg mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Available Times Summary:</h3>
            <p class="text-blue-700 text-sm">
                Found <strong>${totalAvailable} available time slots</strong> for ${serviceName} on ${formattedDate}
            </p>
        </div>
    `;
}

// Build no availability HTML
function buildNoAvailabilityHTML() {
    return `
        <div class="text-center py-8">
            <div class="text-red-600 text-lg font-semibold mb-2">No Available Times</div>
            <p class="text-gray-600">All time slots are fully booked for this date and service. Please try a different date.</p>
        </div>
    `;
}

// Build closed on Sunday HTML
function buildClosedSundayHTML() {
    return '<div class="text-center text-red-600 py-8">We are closed on Sundays</div>';
}

// Export functions for global use
window.toggleUnavailableSection = toggleUnavailableSection;
window.buildAvailableTimesHTML = buildAvailableTimesHTML;
window.buildLimitedTimesHTML = buildLimitedTimesHTML;
window.buildUnavailableTimesHTML = buildUnavailableTimesHTML;
window.buildSummaryHTML = buildSummaryHTML;
window.buildNoAvailabilityHTML = buildNoAvailabilityHTML;
window.buildClosedSundayHTML = buildClosedSundayHTML;
