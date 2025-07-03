
import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { Appointment, ContactMessage } from '@/types/admin';

export const useAdminData = () => {
  const { toast } = useToast();
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [contactMessages, setContactMessages] = useState<ContactMessage[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchData = async () => {
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
  };

  const refetchData = async () => {
    console.log('Refetching admin data...');
    setLoading(true);
    await fetchData();
  };

  const updateAppointmentAssignment = async (id: string, employeeId: string | null) => {
    try {
      console.log('Updating appointment assignment:', { id, employeeId });
      
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({ assigned_employee_id: employeeId })
        .eq('id', id);

      if (error) throw error;

      // Update local state immediately for better UX
      setAppointments(prev => 
        prev.map(apt => apt.id === id ? { ...apt, assigned_employee_id: employeeId } : apt)
      );

      toast({
        title: "Assignment Updated",
        description: "Employee assignment has been updated.",
      });

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

    // Set up real-time subscriptions for automatic updates
    const appointmentsChannel = supabase
      .channel('admin-appointments-changes')
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all changes (INSERT, UPDATE, DELETE)
          schema: 'public',
          table: 'oregon_tires_appointments'
        },
        (payload) => {
          console.log('Real-time appointment change:', payload);
          refetchData(); // Refresh data when changes occur
        }
      )
      .subscribe();

    const messagesChannel = supabase
      .channel('admin-messages-changes')
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all changes
          schema: 'public',
          table: 'oregon_tires_contact_messages'
        },
        (payload) => {
          console.log('Real-time message change:', payload);
          refetchData(); // Refresh data when changes occur
        }
      )
      .subscribe();

    const employeesChannel = supabase
      .channel('admin-employees-changes')
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all changes
          schema: 'public',
          table: 'oregon_tires_employees'
        },
        (payload) => {
          console.log('Real-time employee change:', payload);
          refetchData(); // Refresh data when employee changes occur
        }
      )
      .subscribe();

    const customHoursChannel = supabase
      .channel('admin-custom-hours-changes')
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all changes
          schema: 'public',
          table: 'oregon_tires_custom_hours'
        },
        (payload) => {
          console.log('Real-time custom hours change:', payload);
          refetchData(); // Refresh data when custom hours change
        }
      )
      .subscribe();

    // Cleanup function to remove subscriptions
    return () => {
      supabase.removeChannel(appointmentsChannel);
      supabase.removeChannel(messagesChannel);
      supabase.removeChannel(employeesChannel);
      supabase.removeChannel(customHoursChannel);
    };
  }, []);

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
