import { useState, useEffect, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';

export interface EmployeeAppointmentSummary {
  employee_id: string;
  upcoming_count: number;
  next_appointment_date: string | null;
  next_appointment_service: string | null;
}

export const useEmployeeAppointments = () => {
  const [appointmentSummaries, setAppointmentSummaries] = useState<EmployeeAppointmentSummary[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchEmployeeAppointments = useCallback(async () => {
    try {
      const today = new Date().toISOString().split('T')[0];
      
      const { data: appointments, error } = await supabase
        .from('oretir_appointments')
        .select('assigned_employee_id, preferred_date, service, status')
        .not('assigned_employee_id', 'is', null)
        .gte('preferred_date', today)
        .in('status', ['confirmed', 'scheduled', 'new'])
        .order('preferred_date');

      if (error) throw error;

      // Group appointments by employee
      const summaries: Record<string, EmployeeAppointmentSummary> = {};
      
      (appointments || []).forEach(appointment => {
        const employeeId = appointment.assigned_employee_id!;
        
        if (!summaries[employeeId]) {
          summaries[employeeId] = {
            employee_id: employeeId,
            upcoming_count: 0,
            next_appointment_date: null,
            next_appointment_service: null
          };
        }
        
        summaries[employeeId].upcoming_count++;
        
        // Set next appointment info for the first (earliest) appointment
        if (!summaries[employeeId].next_appointment_date) {
          summaries[employeeId].next_appointment_date = appointment.preferred_date;
          summaries[employeeId].next_appointment_service = appointment.service;
        }
      });

      setAppointmentSummaries(Object.values(summaries));
    } catch (error) {
      console.error('Error fetching employee appointments:', error);
      toast({
        title: "Error",
        description: "Failed to load employee appointments",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchEmployeeAppointments();

    // Set up real-time subscription for appointment changes
    const channelId = Math.random().toString(36).substr(2, 9);
    const channel = supabase
      .channel(`employee-appointments-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'oretir_appointments'
        },
        () => {
          setTimeout(() => fetchEmployeeAppointments(), 100);
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [fetchEmployeeAppointments]);

  const getEmployeeAppointmentSummary = useCallback((employeeId: string): EmployeeAppointmentSummary | null => {
    return appointmentSummaries.find(summary => summary.employee_id === employeeId) || null;
  }, [appointmentSummaries]);

  return {
    appointmentSummaries,
    loading: loading,
    getEmployeeAppointmentSummary,
    refetch: fetchEmployeeAppointments
  };
};