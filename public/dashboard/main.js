
import { state } from './config.js';
import { fetchDataFromSupabase } from './supabase-client.js';
import { generateCalendar, updateSelectedDateInfo } from './calendar.js';
import { loadAppointments, loadMessages } from './data-display.js';
import { toggleLanguage, switchTab, updateStatus, switchView, updateDayView, updateAnalytics } from './ui-controls.js';

// Initialize the page
async function init() {
    try {
        const data = await fetchDataFromSupabase();
        state.appointments = data.appointments;
        state.contactMessages = data.contactMessages;
        
        setTimeout(() => {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('main-content').classList.remove('hidden');
            
            generateCalendar();
            loadAppointments();
            loadMessages();
            updateSelectedDateInfo();
            
            // Set default date for day view
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('day-view-date-picker').value = today;
        }, 500);
    } catch (error) {
        console.error('Error loading data:', error);
        document.getElementById('loading').innerHTML = '<p style="color: red;">Error loading dashboard data. Please refresh the page.</p>';
    }
}

// Make functions available globally
window.toggleLanguage = toggleLanguage;
window.switchTab = switchTab;
window.updateStatus = updateStatus;
window.switchView = switchView;
window.updateDayView = updateDayView;
window.updateAnalytics = updateAnalytics;

// Initialize the page when loaded
window.onload = init;
