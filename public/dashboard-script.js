
// Supabase configuration
const SUPABASE_URL = 'https://vtknmauyvmuaryttnenx.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ0a25tYXV5dm11YXJ5dHRuZW54Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MDE1MDgsImV4cCI6MjA2NTA3NzUwOH0._bOyuxj1nRBw3U7Q3qCsDubBNg_EM-VLhQB0y5p9okY';

// Initialize Supabase client
const { createClient } = supabase;
const supabaseClient = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

// Global variables
let appointments = [];
let contactMessages = [];
let currentLanguage = 'english';
let selectedDate = new Date();
let currentTab = 'appointments';

// Initialize the page
async function init() {
    try {
        await fetchDataFromSupabase();
        
        setTimeout(() => {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('main-content').classList.remove('hidden');
            
            generateCalendar();
            loadAppointments();
            loadMessages();
            updateSelectedDateInfo();
        }, 500);
    } catch (error) {
        console.error('Error loading data:', error);
        document.getElementById('loading').innerHTML = '<p style="color: red;">Error loading dashboard data. Please refresh the page.</p>';
    }
}

// Fetch data from Supabase
async function fetchDataFromSupabase() {
    console.log('Fetching data from Supabase...');
    
    try {
        // Fetch appointments
        const { data: appointmentsData, error: appointmentsError } = await supabaseClient
            .from('oregon_tires_appointments')
            .select('*')
            .order('created_at', { ascending: false });

        if (appointmentsError) {
            console.error('Error fetching appointments:', appointmentsError);
            throw appointmentsError;
        }

        // Fetch contact messages
        const { data: messagesData, error: messagesError } = await supabaseClient
            .from('oregon_tires_contact_messages')
            .select('*')
            .order('created_at', { ascending: false });

        if (messagesError) {
            console.error('Error fetching messages:', messagesError);
            throw messagesError;
        }

        appointments = appointmentsData || [];
        contactMessages = messagesData || [];
        
        console.log('Fetched appointments:', appointments.length);
        console.log('Fetched messages:', contactMessages.length);
    } catch (error) {
        console.error('Supabase fetch error:', error);
        throw error;
    }
}

// Update appointment status in Supabase
async function updateAppointmentStatus(id, newStatus) {
    try {
        console.log('Updating appointment status:', { id, status: newStatus.toLowerCase() });
        
        const { error } = await supabaseClient
            .from('oregon_tires_appointments')
            .update({ status: newStatus.toLowerCase() })
            .eq('id', id);

        if (error) {
            console.error('Error updating appointment status:', error);
            throw error;
        }

        // Update local data
        const appointment = appointments.find(apt => apt.id === id);
        if (appointment) {
            appointment.status = newStatus.toLowerCase();
            updateSelectedDateInfo(); // Refresh the calendar info
        }
        
        console.log('Status updated successfully');
    } catch (error) {
        console.error('Failed to update appointment status:', error);
        alert('Failed to update status. Please try again.');
    }
}

// Update message status in Supabase
async function updateMessageStatus(id, newStatus) {
    try {
        console.log('Updating message status:', { id, status: newStatus.toLowerCase() });
        
        const { error } = await supabaseClient
            .from('oregon_tires_contact_messages')
            .update({ status: newStatus.toLowerCase() })
            .eq('id', id);

        if (error) {
            console.error('Error updating message status:', error);
            throw error;
        }

        // Update local data
        const message = contactMessages.find(msg => msg.id === id);
        if (message) {
            message.status = newStatus.toLowerCase();
        }
        
        console.log('Message status updated successfully');
    } catch (error) {
        console.error('Failed to update message status:', error);
        alert('Failed to update status. Please try again.');
    }
}

// Language toggle
function toggleLanguage() {
    currentLanguage = currentLanguage === 'english' ? 'spanish' : 'english';
    console.log('Language switched to:', currentLanguage);
}

// Tab switching
function switchTab(tabName) {
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

    currentTab = tabName;
}

// Calendar generation
function generateCalendar() {
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
            if (appointments.some(apt => apt.preferred_date === dateStr)) {
                cell.classList.add('has-appointment');
            }

            row.appendChild(cell);
        }
        
        calendarBody.appendChild(row);
    }
}

// Date selection
function selectDate(date) {
    selectedDate = date;
    updateSelectedDateInfo();
}

// Update selected date info
function updateSelectedDateInfo() {
    const dateStr = selectedDate.toLocaleDateString();
    document.getElementById('selected-date').textContent = dateStr;

    const dateIsoStr = selectedDate.toISOString().split('T')[0];
    const dayAppointments = appointments.filter(apt => apt.preferred_date === dateIsoStr);
    
    document.getElementById('appointment-count').textContent = `${dayAppointments.length} appointments`;

    const appointmentList = document.getElementById('appointment-list');
    if (dayAppointments.length === 0) {
        appointmentList.innerHTML = '<p style="color: #6b7280; font-size: 0.875rem;">No appointments scheduled for this date</p>';
    } else {
        appointmentList.innerHTML = dayAppointments.map(apt => `
            <div class="appointment-item">
                <div style="font-weight: 500;">${apt.first_name} ${apt.last_name}</div>
                <div style="color: #6b7280;">${apt.service} - ${apt.preferred_time}</div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                    <span class="status-badge status-${apt.status}">${capitalizeStatus(apt.status)}</span>
                    <select class="status-select" onchange="updateStatus('appointment', '${apt.id}', this.value)">
                        <option value="new" ${apt.status === 'new' ? 'selected' : ''}>New</option>
                        <option value="priority" ${apt.status === 'priority' ? 'selected' : ''}>Priority</option>
                        <option value="completed" ${apt.status === 'completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </div>
            </div>
        `).join('');
    }
}

// Load appointments
function loadAppointments() {
    const tbody = document.getElementById('appointments-table');
    
    if (appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="color: #6b7280; padding: 1.5rem;">No appointments found.</td></tr>';
        return;
    }

    tbody.innerHTML = appointments.map(appointment => `
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
    `).join('');
}

// Load messages
function loadMessages() {
    const tbody = document.getElementById('messages-table');
    
    if (contactMessages.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="color: #6b7280; padding: 1.5rem;">No messages found.</td></tr>';
        return;
    }

    tbody.innerHTML = contactMessages.map(message => `
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

// Update status (unified function for both appointments and messages)
async function updateStatus(type, id, newStatus) {
    if (type === 'appointment') {
        await updateAppointmentStatus(id, newStatus);
    } else if (type === 'message') {
        await updateMessageStatus(id, newStatus);
    }
}

// Utility functions
function capitalizeStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

// Initialize the page when loaded
window.onload = init;
