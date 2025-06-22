
// Supabase client initialization
const { createClient } = supabase;
const supabaseClient = createClient(
    'https://vtknmauyvmuaryttnenx.supabase.co',
    'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ0a25tYXV5dm11YXJ5dHRuZW54Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDk1MDE1MDgsImV4cCI6MjA2NTA3NzUwOH0._bOyuxj1nRBw3U7Q3qCsDubBNg_EM-VLhQB0y5p9okY'
);

// Export for use in other modules
window.supabaseClient = supabaseClient;
