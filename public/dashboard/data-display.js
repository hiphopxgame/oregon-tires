
import { state, serviceDurations } from './config.js';

// Load appointments
export function loadAppointments() {
    const tbody = document.getElementById('appointments-table');
    
    if (state.appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="color: #6b7280; padding: 1.5rem;">No appointments found.</td></tr>';
        return;
    }

    tbody.innerHTML = state.appointments.map(appointment => {
        const duration = getServiceDuration(appointment.service);
        const formattedDuration = formatDuration(duration);
        return `
            <tr>
                <td>
                    <div>
                        <p class="customer-name">${appointment.first_name} ${appointment.last_name}</p>
                        <p class="contact-info">${appointment.phone}</p>
                        <p class="contact-info">${appointment.email}</p>
                        ${appointment.message ? `<p class="contact-info message-truncate" style="margin-top: 0.25rem;">${appointment.message}</p>` : ''}
                    </div>
                </td>
                <td>
                    <span style="font-weight: 500;">${appointment.service}</span>
                    <div style="font-size: 0.875rem; color: #6b7280;">${formattedDuration}</div>
                </td>
                <td>
                    <div style="font-size: 0.875rem;">
                        <p style="font-weight: 500;">${appointment.preferred_date}</p>
                        <p style="color: #6b7280;">${appointment.preferred_time}</p>
                    </div>
                </td>
                <td>
                    <select class="status-select" onchange="updateStatus('appointment', '${appointment.id}', this.value)">
                        <option value="new" ${appointment.status === 'new' ? 'selected' : ''}>New</option>
                        <option value="priority" ${appointment.status === 'priority' ? 'selected' : ''}>Priority</option>
                        <option value="completed" ${appointment.status === 'completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </td>
            </tr>
        `;
    }).join('');
}

// Load messages
export function loadMessages() {
    const tbody = document.getElementById('messages-table');
    
    if (state.contactMessages.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="color: #6b7280; padding: 1.5rem;">No messages found.</td></tr>';
        return;
    }

    tbody.innerHTML = state.contactMessages.map(message => `
        <tr>
            <td>
                <div>
                    <p class="customer-name">${message.first_name} ${message.last_name}</p>
                </div>
            </td>
            <td>
                <div style="font-size: 0.875rem;">
                    <p>${message.email}</p>
                    ${message.phone ? `<p>${message.phone}</p>` : ''}
                </div>
            </td>
            <td>
                <p style="font-size: 0.875rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">${message.message}</p>
            </td>
            <td>
                <p style="font-size: 0.875rem;">${new Date(message.created_at).toLocaleDateString()}</p>
            </td>
            <td>
                <select class="status-select" onchange="updateStatus('message', '${message.id}', this.value)">
                    <option value="new" ${message.status === 'new' ? 'selected' : ''}>New</option>
                    <option value="priority" ${message.status === 'priority' ? 'selected' : ''}>Priority</option>
                    <option value="completed" ${message.status === 'completed' ? 'selected' : ''}>Completed</option>
                </select>
            </td>
        </tr>
    `).join('');
}

// Get service duration
function getServiceDuration(service) {
    return serviceDurations[service] || 120; // Default to 2 hours if service not found
}

// Format duration in a readable way (hours and minutes)
function formatDuration(minutes) {
    if (minutes < 60) {
        return `${minutes} minutes`;
    } else {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        if (remainingMinutes === 0) {
            return hours === 1 ? '1 hour' : `${hours} hours`;
        } else {
            return `${hours}h ${remainingMinutes}m`;
        }
    }
}
