
import { state, serviceDurations } from './config.js';

// Load appointments
export function loadAppointments() {
    const tbody = document.getElementById('appointments-table');
    
    if (state.appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="color: #6b7280; padding: 1.5rem;">No appointments found.</td></tr>';
        return;
    }

    tbody.innerHTML = state.appointments.map(appointment => {
        const durationText = getServiceDurationText(appointment.service);
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
                    <div style="font-size: 0.875rem; color: #6b7280;">${durationText}</div>
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

// Get service duration in readable format
function getServiceDurationText(service) {
    const minutes = serviceDurations[service] || 120;
    
    if (minutes < 60) {
        return `${minutes} minutes`;
    } else if (minutes === 60) {
        return '1 hour';
    } else if (minutes === 75) {
        return '1.25 hours';
    } else if (minutes === 150) {
        return '2.5 hours';
    } else if (minutes === 210) {
        return '3.5 hours';
    } else if (minutes === 300) {
        return '5 hours';
    } else {
        const hours = minutes / 60;
        return `${hours} hours`;
    }
}

