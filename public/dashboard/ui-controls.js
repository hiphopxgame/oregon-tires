
import { state } from './config.js';
import { updateAppointmentStatus, updateMessageStatus } from './supabase-client.js';
import { updateSelectedDateInfo } from './calendar.js';
import { loadAppointments, loadMessages } from './data-display.js';

// Language toggle
export function toggleLanguage() {
    state.currentLanguage = state.currentLanguage === 'english' ? 'spanish' : 'english';
    console.log('Language switched to:', state.currentLanguage);
}

// View switching
export function switchView(viewName) {
    // Update nav buttons
    document.querySelectorAll('.nav-button').forEach(button => {
        button.classList.remove('active');
    });
    event.target.classList.add('active');

    // Update view content
    document.querySelectorAll('.view-content').forEach(view => {
        view.classList.remove('active');
    });
    
    const targetView = document.getElementById(viewName + '-view');
    if (targetView) {
        targetView.classList.add('active');
    }

    state.currentView = viewName;

    // Load specific view data
    if (viewName === 'appointments') {
        updateDayView();
    } else if (viewName === 'analytics') {
        updateAnalytics();
    }
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

// Update day view
export function updateDayView() {
    const datePicker = document.getElementById('day-view-date-picker');
    const selectedDate = datePicker.value || new Date().toISOString().split('T')[0];
    const dayViewDate = document.getElementById('day-view-date');
    const dayViewContent = document.getElementById('day-view-content');
    
    dayViewDate.textContent = new Date(selectedDate).toLocaleDateString();
    
    const dayAppointments = state.appointments.filter(apt => apt.preferred_date === selectedDate);
    
    if (dayAppointments.length === 0) {
        dayViewContent.innerHTML = '<p style="color: #6b7280;">No appointments scheduled for this date</p>';
        return;
    }

    const timeSlots = {};
    for (let hour = 7; hour <= 19; hour++) {
        const timeString = `${hour.toString().padStart(2, '0')}:00`;
        timeSlots[timeString] = [];
    }

    dayAppointments.forEach(appointment => {
        const timeSlot = appointment.preferred_time.substring(0, 5);
        if (timeSlots[timeSlot]) {
            timeSlots[timeSlot].push(appointment);
        }
    });

    let html = '<div class="time-slots">';
    Object.entries(timeSlots).forEach(([time, appointments]) => {
        html += `<div class="time-slot">`;
        html += `<div class="time-label">${time}</div>`;
        html += `<div class="appointments">`;
        
        if (appointments.length === 0) {
            html += `<div class="no-appointment">Available</div>`;
        } else {
            appointments.forEach(appointment => {
                html += `
                    <div class="appointment-card">
                        <div class="appointment-customer">${appointment.first_name} ${appointment.last_name}</div>
                        <div class="appointment-service">${appointment.service}</div>
                        <div class="appointment-contact">${appointment.phone}</div>
                        <select class="status-select" onchange="updateStatus('appointment', '${appointment.id}', this.value)">
                            <option value="new" ${appointment.status === 'new' ? 'selected' : ''}>New</option>
                            <option value="priority" ${appointment.status === 'priority' ? 'selected' : ''}>Priority</option>
                            <option value="completed" ${appointment.status === 'completed' ? 'selected' : ''}>Completed</option>
                        </select>
                    </div>
                `;
            });
        }
        
        html += `</div></div>`;
    });
    html += '</div>';

    dayViewContent.innerHTML = html;
}

// Update analytics
export function updateAnalytics() {
    const totalAppointments = state.appointments.length;
    const pendingAppointments = state.appointments.filter(apt => apt.status === 'new' || apt.status === 'pending').length;
    const completedAppointments = state.appointments.filter(apt => apt.status === 'completed').length;
    const totalMessages = state.contactMessages.length;

    document.getElementById('total-appointments').textContent = totalAppointments;
    document.getElementById('pending-appointments').textContent = pendingAppointments;
    document.getElementById('completed-appointments').textContent = completedAppointments;
    document.getElementById('total-messages').textContent = totalMessages;

    // Popular services
    const serviceCount = {};
    state.appointments.forEach(apt => {
        serviceCount[apt.service] = (serviceCount[apt.service] || 0) + 1;
    });

    const popularServicesList = document.getElementById('popular-services-list');
    const sortedServices = Object.entries(serviceCount)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 5);

    if (sortedServices.length === 0) {
        popularServicesList.innerHTML = '<p style="color: #6b7280;">No services data available</p>';
    } else {
        popularServicesList.innerHTML = sortedServices.map(([service, count]) => 
            `<div class="service-stat"><span>${service}</span><span>${count}</span></div>`
        ).join('');
    }
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
            if (state.currentView === 'appointments') {
                updateDayView();
            } else if (state.currentView === 'analytics') {
                updateAnalytics();
            }
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

// Make functions available globally
window.switchView = switchView;
window.updateDayView = updateDayView;
