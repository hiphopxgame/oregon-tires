
// Time slot management functionality

// Generate standard time slots from 7 AM to 7 PM
function generateTimeSlots() {
    const timeSlots = [];
    for (let hour = 7; hour < 19; hour++) {
        timeSlots.push(`${hour.toString().padStart(2, '0')}:00`);
        timeSlots.push(`${hour.toString().padStart(2, '0')}:30`);
    }
    return timeSlots;
}

// Convert 24-hour time to 12-hour display format
function formatTimeDisplay(time) {
    const hour = parseInt(time.split(':')[0]);
    const minute = time.split(':')[1];
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:${minute} ${ampm}`;
}

// Check availability for all time slots for a given date and service
async function checkAllTimeSlotAvailability(date, service) {
    const timeSlots = generateTimeSlots();
    const availableTimes = [];
    const limitedTimes = [];
    const unavailableTimes = [];

    for (const time of timeSlots) {
        const conflict = await window.checkAppointmentConflicts(date, time, service);
        
        const timeInfo = {
            time: time,
            display: formatTimeDisplay(time),
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

    return {
        available: availableTimes,
        limited: limitedTimes,
        unavailable: unavailableTimes,
        total: availableTimes.length + limitedTimes.length
    };
}

// Export functions for global use
window.generateTimeSlots = generateTimeSlots;
window.formatTimeDisplay = formatTimeDisplay;
window.checkAllTimeSlotAvailability = checkAllTimeSlotAvailability;
