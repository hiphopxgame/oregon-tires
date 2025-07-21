import { useState, useEffect, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';

export interface Employee {
  id: string;
  name: string;
  email: string | null;
  phone: string | null;
  role: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export const useEmployees = () => {
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchEmployees = useCallback(async () => {
    try {
      const { data, error } = await supabase
        .from('oregon_tires_employees')
        .select('*')
        .order('name');

      if (error) throw error;
      setEmployees(data || []);
    } catch (error) {
      console.error('Error fetching employees:', error);
      toast({
        title: "Error",
        description: "Failed to load employees",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  }, [toast]);

  useEffect(() => {
    fetchEmployees();

    // Set up real-time subscription for employee changes with unique channel name
    const channelId = Math.random().toString(36).substr(2, 9);
    const channel = supabase
      .channel(`employee-manager-changes-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'oregon_tires_employees'
        },
        (payload) => {
          console.log('Real-time employee change detected:', payload);
          setTimeout(() => fetchEmployees(), 100); // Small delay to avoid rapid calls
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [fetchEmployees]);

  return {
    employees,
    loading,
    refetch: fetchEmployees
  };
};