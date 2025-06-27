
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
    await fetchData();
  };

  const updateAppointmentStatus = async (id: string, status: string) => {
    try {
      console.log('Updating appointment status:', { id, status });
      
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({ status })
        .eq('id', id);

      if (error) throw error;

      // Update local state immediately
      setAppointments(prev => 
        prev.map(apt => apt.id === id ? { ...apt, status } : apt)
      );

      toast({
        title: "Status Updated",
        description: "Appointment status has been updated.",
      });

      // Refetch data to ensure consistency
      await refetchData();
    } catch (error) {
      console.error('Error updating appointment status:', error);
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
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

      // Refetch data to ensure consistency
      await refetchData();
    } catch (error) {
      console.error('Error updating message status:', error);
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  return {
    appointments,
    contactMessages,
    loading,
    updateAppointmentStatus,
    updateMessageStatus,
    refetchData
  };
};
