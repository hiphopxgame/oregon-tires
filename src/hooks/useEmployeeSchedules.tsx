import { useState, useEffect, useCallback } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';

export interface EmployeeSchedule {
  id: string;
  employee_id: string;
  day_of_week: number; // 0 = Sunday, 6 = Saturday
  start_time: string;
  end_time: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface EmployeeWithSchedule {
  id: string;
  name: string;
  email: string | null;
  phone: string | null;
  role: string;
  is_active: boolean;
  schedules: EmployeeSchedule[];
}

export const useEmployeeSchedules = () => {
  const [employeesWithSchedules, setEmployeesWithSchedules] = useState<EmployeeWithSchedule[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchEmployeesWithSchedules = useCallback(async () => {
    try {
      // Fetch employees
      const { data: employees, error: employeesError } = await supabase
        .from('oregon_tires_employees')
        .select('*')
        .order('name');

      if (employeesError) throw employeesError;

      // Fetch all schedules
      const { data: schedules, error: schedulesError } = await supabase
        .from('oregon_tires_employee_schedules')
        .select('*')
        .eq('is_active', true)
        .order('day_of_week');

      if (schedulesError) throw schedulesError;

      // Combine employees with their schedules
      const employeesWithSchedules = (employees || []).map(employee => ({
        ...employee,
        schedules: (schedules || []).filter(schedule => schedule.employee_id === employee.id) as EmployeeSchedule[]
      }));

      setEmployeesWithSchedules(employeesWithSchedules);
    } catch (error) {
      console.error('Error fetching employees with schedules:', error);
      toast({
        title: "Error",
        description: "Failed to load employee schedules",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  }, []);

  const saveEmployeeSchedule = useCallback(async (
    employeeId: string, 
    dayOfWeek: number, 
    startTime: string, 
    endTime: string
  ) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_employee_schedules')
        .upsert({
          employee_id: employeeId,
          day_of_week: dayOfWeek,
          start_time: startTime,
          end_time: endTime,
          is_active: true
        }, {
          onConflict: 'employee_id,day_of_week'
        });

      if (error) throw error;

      await fetchEmployeesWithSchedules();
      
      toast({
        title: "Success",
        description: "Schedule updated successfully",
      });
    } catch (error) {
      console.error('Error saving schedule:', error);
      toast({
        title: "Error",
        description: "Failed to save schedule",
        variant: "destructive",
      });
    }
  }, [fetchEmployeesWithSchedules]);

  const deleteEmployeeSchedule = useCallback(async (employeeId: string, dayOfWeek: number) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_employee_schedules')
        .delete()
        .eq('employee_id', employeeId)
        .eq('day_of_week', dayOfWeek);

      if (error) throw error;

      await fetchEmployeesWithSchedules();
      
      toast({
        title: "Success",
        description: "Schedule deleted successfully",
      });
    } catch (error) {
      console.error('Error deleting schedule:', error);
      toast({
        title: "Error",
        description: "Failed to delete schedule",
        variant: "destructive",
      });
    }
  }, [fetchEmployeesWithSchedules]);

  const isEmployeeScheduled = useCallback((employeeId: string, date: Date): boolean => {
    const employee = employeesWithSchedules.find(emp => emp.id === employeeId);
    if (!employee) return false;

    const dayOfWeek = date.getDay(); // 0 = Sunday, 6 = Saturday
    return employee.schedules.some(schedule => 
      schedule.day_of_week === dayOfWeek && schedule.is_active
    );
  }, [employeesWithSchedules]);

  const getEmployeeScheduleForDate = useCallback((employeeId: string, date: Date): EmployeeSchedule | null => {
    const employee = employeesWithSchedules.find(emp => emp.id === employeeId);
    if (!employee) return null;

    const dayOfWeek = date.getDay();
    return employee.schedules.find(schedule => 
      schedule.day_of_week === dayOfWeek && schedule.is_active
    ) || null;
  }, [employeesWithSchedules]);

  useEffect(() => {
    fetchEmployeesWithSchedules();

    // Set up real-time subscription
    const channelId = Math.random().toString(36).substr(2, 9);
    const channel = supabase
      .channel(`employee-schedules-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'oregon_tires_employee_schedules'
        },
        () => {
          setTimeout(() => fetchEmployeesWithSchedules(), 100);
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [fetchEmployeesWithSchedules]);

  return {
    employeesWithSchedules,
    loading,
    saveEmployeeSchedule,
    deleteEmployeeSchedule,
    isEmployeeScheduled,
    getEmployeeScheduleForDate,
    refetch: fetchEmployeesWithSchedules
  };
};