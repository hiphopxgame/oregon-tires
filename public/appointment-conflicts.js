
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
        const { data: appointments, error } = await window.supabaseClient
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

// Export functions for global use
window.checkAppointmentConflicts = checkAppointmentConflicts;
window.updateTimeSlotAvailability = updateTimeSlotAvailability;
window.serviceDurations = serviceDurations;
