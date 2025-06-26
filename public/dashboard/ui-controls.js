
import { state } from './config.js';
import { updateAppointmentStatus, updateMessageStatus } from './supabase-client.js';
import { updateSelectedDateInfo } from './calendar.js';

// Language toggle
export function toggleLanguage() {
    state.currentLanguage = state.currentLanguage === 'english' ? 'spanish' : 'english';
    console.log('Language switched to:', state.currentLanguage);
}

// Tab switching
export function switchTab(tabName) {
    // Update tab triggers
    document.querySelectorAll('.tab-trigger').forEach(trigger => {
        trigger.classList.remove('active');
    });
    event.target.classList.add('active');

    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName + '-tab').classList.add('active');

    state.currentTab = tabName;
}

// Update status (unified function for both appointments and messages)
export async function updateStatus(type, id, newStatus) {
    if (type === 'appointment') {
        await updateAppointmentStatus(id, newStatus);
        // Update local data
        const appointment = state.appointments.find(apt => apt.id === id);
        if (appointment) {
            appointment.status = newStatus.toLowerCase();
            updateSelectedDateInfo(); // Refresh the calendar info
        }
    } else if (type === 'message') {
        await updateMessageStatus(id, newStatus);
        // Update local data
        const message = state.contactMessages.find(msg => msg.id === id);
        if (message) {
            message.status = newStatus.toLowerCase();
        }
    }
}
