
// Dashboard configuration
export const SUPABASE_CONFIG = {
    URL: 'https://vtknmauyvmuaryttnenx.supabase.co',
    ANON_KEY: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ0a25tYXV5dm11YXJ5dHRuZW54Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MDE1MDgsImV4cCI6MjA2NTA3NzUwOH0._bOyuxj1nRBw3U7Q3qCsDubBNg_EM-VLhQB0y5p9okY'
};

// Service duration mapping (in minutes)
export const serviceDurations = {
    'Tire Repair': 60,
    'Oil Change': 75,
    'Alignment': 120,
    'Brake Change (Front or Back)': 120,
    'Tires (New or Used)': 120,
    'Tire Mount and Balance': 120,
    'Mechanical Inspection and Estimate': 150,
    'Brake Change (Front and Back)': 210,
    'Tuneup': 300,
    // Legacy mappings for backward compatibility
    'Tire Installation': 120,
    'Tire Rotation & Balancing': 120,
    'Wheel Alignment': 120,
    'Brake Service': 120,
    'Other Service': 120
};

// Global state
export const state = {
    appointments: [],
    contactMessages: [],
    currentLanguage: 'english',
    selectedDate: new Date(),
    currentTab: 'appointments'
};
