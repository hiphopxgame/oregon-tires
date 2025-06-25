
// Dashboard configuration
export const SUPABASE_CONFIG = {
    URL: 'https://vtknmauyvmuaryttnenx.supabase.co',
    ANON_KEY: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ0a25tYXV5dm11YXJ5dHRuZW54Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MDE1MDgsImV4cCI6MjA2NTA3NzUwOH0._bOyuxj1nRBw3U7Q3qCsDubBNg_EM-VLhQB0y5p9okY'
};

// Service duration mapping (in minutes)
export const serviceDurations = {
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

// Global state
export const state = {
    appointments: [],
    contactMessages: [],
    currentLanguage: 'english',
    selectedDate: new Date(),
    currentTab: 'appointments'
};
