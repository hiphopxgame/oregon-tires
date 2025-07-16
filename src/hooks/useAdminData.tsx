
import { useState, useEffect, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { Appointment, ContactMessage } from '@/types/admin';
import { useEmailNotifications } from './useEmailNotifications';

export const useAdminData = () => {
  const { toast } = useToast();
  const { sendAppointmentEmail } = useEmailNotifications();
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [contactMessages, setContactMessages] = useState<ContactMessage[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchData = useCallback(async () => {
    try {
      console.log('Fetching admin data...');
      const [appointmentsRes, contactRes] = await Promise.all([
        supabase
          .from('oregon_tires_appointments')
          .select('*')
          .order('created_at', { ascending: false }),
        supabase
          .from('oregon_tires_contact_messages')
          .select('*')
          .order('created_at', { ascending: false })
      ]);

      if (appointmentsRes.error) throw appointmentsRes.error;
      if (contactRes.error) throw contactRes.error;

      console.log('Fetched appointments:', appointmentsRes.data?.length || 0);
      console.log('Appointments data:', appointmentsRes.data);
      console.log('Fetched messages:', contactRes.data?.length || 0);

      setAppointments(appointmentsRes.data || []);
      setContactMessages(contactRes.data || []);
    } catch (error) {
      console.error('Error fetching data:', error);
      toast({
        title: "Error",
        description: "Failed to load data",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  }, [toast]);

  const refetchData = useCallback(async () => {
    console.log('Refetching admin data...');
    setLoading(true);
    await fetchData();
  }, [fetchData]);

  const updateAppointmentAssignment = async (id: string, employeeId: string | null) => {
    try {
      console.log('Updating appointment assignment:', { id, employeeId });
      
      // When assigning employee, also update status to confirmed
      const updateData: any = { assigned_employee_id: employeeId };
      if (employeeId) {
        updateData.status = 'confirmed';
      }
      
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update(updateData)
        .eq('id', id);

      if (error) throw error;

      // Update local state immediately for better UX
      setAppointments(prev => 
        prev.map(apt => apt.id === id ? { 
          ...apt, 
          assigned_employee_id: employeeId,
          status: employeeId ? 'confirmed' : apt.status 
        } : apt)
      );

      toast({
        title: "Assignment Updated",
        description: employeeId ? "Employee assigned and status updated to Confirmed." : "Employee assignment removed.",
      });

      // Send email notification to employee when assigned
      if (employeeId) {
        try {
          await sendAppointmentEmail('appointment_assigned', id);
        } catch (emailError) {
          console.error('Failed to send assignment email:', emailError);
        }
      }

      // Force a fresh data fetch to ensure consistency
      setTimeout(() => {
        refetchData();
      }, 500);
    } catch (error) {
      console.error('Error updating appointment assignment:', error);
      toast({
        title: "Error",
        description: "Failed to update assignment",
        variant: "destructive",
      });
      // Revert local state on error
      await refetchData();
    }
  };

  const updateAppointmentStatus = async (id: string, status: string) => {
    try {
      console.log('Updating appointment status:', { id, status });
      
      // Validate that employee is assigned when confirming appointment
      if (status === 'confirmed') {
        const appointment = appointments.find(apt => apt.id === id);
        if (!appointment?.assigned_employee_id) {
          toast({
            title: "Employee Required",
            description: "You must assign an employee before confirming an appointment.",
            variant: "destructive",
          });
          return;
        }
      }
      
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({ status })
        .eq('id', id);

      if (error) throw error;

      // Update local state immediately for better UX
      setAppointments(prev => 
        prev.map(apt => apt.id === id ? { ...apt, status } : apt)
      );

      toast({
        title: "Status Updated",
        description: "Appointment status has been updated.",
      });

      // Send email notification when appointment is completed
      if (status === 'completed') {
        try {
          await sendAppointmentEmail('appointment_completed', id);
        } catch (emailError) {
          console.error('Failed to send completion email:', emailError);
        }
      }

      // Force a fresh data fetch to ensure consistency
      setTimeout(() => {
        refetchData();
      }, 500);
    } catch (error) {
      console.error('Error updating appointment status:', error);
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
      // Revert local state on error
      await refetchData();
    }
  };

  const updateMessageStatus = async (id: string, status: string) => {
    try {
      console.log('Updating message status:', { id, status });
      
      const { error } = await supabase
        .from('oregon_tires_contact_messages')
        .update({ status })
        .eq('id', id);

      if (error) throw error;

      // Update local state immediately
      setContactMessages(prev => 
        prev.map(msg => msg.id === id ? { ...msg, status } : msg)
      );

      toast({
        title: "Status Updated",
        description: "Message status has been updated.",
      });

      // Force a fresh data fetch to ensure consistency
      setTimeout(() => {
        refetchData();
      }, 500);
    } catch (error) {
      console.error('Error updating message status:', error);
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
      // Revert local state on error
      await refetchData();
    }
  };

  useEffect(() => {
    fetchData();

    // Create unique channel names to avoid conflicts
    const channelId = Math.random().toString(36).substr(2, 9);
    
    // Set up real-time subscriptions for automatic updates with unique channel names
    const appointmentsChannel = supabase
      .channel(`admin-dashboard-appointments-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all changes (INSERT, UPDATE, DELETE)
          schema: 'public',
          table: 'oregon_tires_appointments'
        },
        (payload) => {
          console.log('Real-time appointment change:', payload);
          // Use a small delay to avoid rapid successive calls
          setTimeout(() => refetchData(), 100);
        }
      )
      .subscribe();

    const messagesChannel = supabase
      .channel(`admin-dashboard-messages-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all changes
          schema: 'public',
          table: 'oregon_tires_contact_messages'
        },
        (payload) => {
          console.log('Real-time message change:', payload);
          setTimeout(() => refetchData(), 100);
        }
      )
      .subscribe();

    // Cleanup function to remove subscriptions
    return () => {
      supabase.removeChannel(appointmentsChannel);
      supabase.removeChannel(messagesChannel);
    };
  }, [fetchData, refetchData]);

  return {
    appointments,
    contactMessages,
    loading,
    updateAppointmentStatus,
    updateAppointmentAssignment,
    updateMessageStatus,
    refetchData
  };
};
