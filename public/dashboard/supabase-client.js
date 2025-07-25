
import { SUPABASE_CONFIG } from './config.js';

// Initialize Supabase client
const { createClient } = supabase;
export const supabaseClient = createClient(SUPABASE_CONFIG.URL, SUPABASE_CONFIG.ANON_KEY);

// Fetch data from Supabase
export async function fetchDataFromSupabase() {
    console.log('Fetching data from Supabase...');
    
    try {
        // Fetch appointments
        const { data: appointmentsData, error: appointmentsError } = await supabaseClient
            .from('oretir_appointments')
            .select('*')
            .order('created_at', { ascending: false });

        if (appointmentsError) {
            console.error('Error fetching appointments:', appointmentsError);
            throw appointmentsError;
        }

        // Fetch contact messages
        const { data: messagesData, error: messagesError } = await supabaseClient
            .from('oretir_contact_messages')
            .select('*')
            .order('created_at', { ascending: false });

        if (messagesError) {
            console.error('Error fetching messages:', messagesError);
            throw messagesError;
        }

        return {
            appointments: appointmentsData || [],
            contactMessages: messagesData || []
        };
    } catch (error) {
        console.error('Supabase fetch error:', error);
        throw error;
    }
}

// Update appointment status in Supabase
export async function updateAppointmentStatus(id, newStatus) {
    try {
        console.log('Updating appointment status:', { id, status: newStatus.toLowerCase() });
        
        const { error } = await supabaseClient
            .from('oretir_appointments')
            .update({ status: newStatus.toLowerCase() })
            .eq('id', id);

        if (error) {
            console.error('Error updating appointment status:', error);
            throw error;
        }
        
        console.log('Status updated successfully');
    } catch (error) {
        console.error('Failed to update appointment status:', error);
        alert('Failed to update status. Please try again.');
    }
}

// Update message status in Supabase
export async function updateMessageStatus(id, newStatus) {
    try {
        console.log('Updating message status:', { id, status: newStatus.toLowerCase() });
        
        const { error } = await supabaseClient
            .from('oretir_contact_messages')
            .update({ status: newStatus.toLowerCase() })
            .eq('id', id);

        if (error) {
            console.error('Error updating message status:', error);
            throw error;
        }
        
        console.log('Message status updated successfully');
    } catch (error) {
        console.error('Failed to update message status:', error);
        alert('Failed to update status. Please try again.');
    }
}
