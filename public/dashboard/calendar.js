
import { state } from './config.js';

// Calendar generation
export function generateCalendar() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    
    // Update month display
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    document.getElementById('calendar-month').textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    for (let week = 0; week < 6; week++) {
        const row = document.createElement('tr');
        
        for (let day = 0; day < 7; day++) {
            const cell = document.createElement('td');
            const cellDate = new Date(startDate);
            cellDate.setDate(startDate.getDate() + (week * 7) + day);
            
            cell.textContent = cellDate.getDate();
            cell.onclick = () => selectDate(cellDate);

            // Add classes for styling
            if (cellDate.getMonth() !== month) {
                cell.style.color = '#d1d5db';
            }
            
            if (cellDate.toDateString() === now.toDateString()) {
                cell.classList.add('today');
            }

            // Check if date has appointments
            const dateStr = cellDate.toISOString().split('T')[0];
            if (state.appointments.some(apt => apt.preferred_date === dateStr)) {
                cell.classList.add('has-appointment');
            }

            row.appendChild(cell);
        }
        
        calendarBody.appendChild(row);
    }
}

// Date selection
export function selectDate(date) {
    state.selectedDate = date;
    updateSelectedDateInfo();
}

// Update selected date info
export function updateSelectedDateInfo() {
    const dateStr = state.selectedDate.toLocaleDateString();
    document.getElementById('selected-date').textContent = dateStr;

    const dateIsoStr = state.selectedDate.toISOString().split('T')[0];
    const dayAppointments = state.appointments.filter(apt => apt.preferred_date === dateIsoStr);
    
    document.getElementById('appointment-count').textContent = `${dayAppointments.length} appointments`;

    const appointmentList = document.getElementById('appointment-list');
    if (dayAppointments.length === 0) {
        appointmentList.innerHTML = '<p style="color: #6b7280; font-size: 0.875rem;">No appointments scheduled for this date</p>';
    } else {
        appointmentList.innerHTML = dayAppointments.map(apt => {
            const duration = getServiceDuration(apt.service);
            return `
                <div class="appointment-item">
                    <div style="font-weight: 500;">${apt.first_name} ${apt.last_name}</div>
                    <div style="color: #6b7280;">${apt.service} - ${apt.preferred_time} (${duration} min)</div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                        <span class="status-badge status-${apt.status}">${capitalizeStatus(apt.status)}</span>
                        <select class="status-select" onchange="updateStatus('appointment', '${apt.id}', this.value)">
                            <option value="new" ${apt.status === 'new' ? 'selected' : ''}>New</option>
                            <option value="priority" ${apt.status === 'priority' ? 'selected' : ''}>Priority</option>
                            <option value="completed" ${apt.status === 'completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                </div>
            `;
        }).join('');
    }
}

// Get service duration
function getServiceDuration(service) {
    const serviceDurations = {
        'Tire Installation (4 tires)': 60,
        'Tire Installation (2 tires)': 45,
        'Tire Installation (1 tire)': 30,
        'Tire Repair': 30,
        'Tire Rotation & Balancing': 45,
        'Wheel Alignment': 60,
        'Brake Service': 90,
        'Oil Change': 30,
        'Other Service': 60
    };
    return serviceDurations[service] || 60;
}

// Utility function
function capitalizeStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
