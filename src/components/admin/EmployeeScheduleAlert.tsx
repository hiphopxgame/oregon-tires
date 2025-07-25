import React from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Calendar, Users } from 'lucide-react';
import { useAdminData } from '@/hooks/useAdminData';
import { useEmployeeSchedules, EmployeeWithSchedule } from '@/hooks/useEmployeeSchedules';

interface EmployeeScheduleAlertProps {
  employee: EmployeeWithSchedule;
}

export const EmployeeScheduleAlert = ({ employee }: EmployeeScheduleAlertProps) => {
  const { appointments } = useAdminData();
  const { isEmployeeScheduled } = useEmployeeSchedules();

  // Find future appointments assigned to this employee where they're not scheduled
  const conflictingAppointments = appointments.filter(appointment => {
    if (appointment.assigned_employee_id !== employee.id) return false;
    
    const appointmentDate = new Date(appointment.preferred_date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Only include future appointments
    if (appointmentDate < today) return false;
    
    return !isEmployeeScheduled(employee.id, appointmentDate);
  });

  if (conflictingAppointments.length === 0) return null;

  const uniqueDates = [...new Set(conflictingAppointments.map(apt => apt.preferred_date))];
  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  return (
    <Alert className="border-orange-200 bg-orange-50 mt-3">
      <AlertTriangle className="h-4 w-4 text-orange-600" />
      <AlertDescription>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="text-orange-800">
              <strong>{employee.name}</strong> has {conflictingAppointments.length} appointment{conflictingAppointments.length > 1 ? 's' : ''} 
              on unscheduled day{uniqueDates.length > 1 ? 's' : ''}
            </span>
            <Badge variant="outline" className="text-orange-700 border-orange-300">
              Schedule Needed
            </Badge>
          </div>
          <div className="flex items-center gap-1 text-sm text-orange-700">
            <Calendar className="h-3 w-3" />
            <span>{uniqueDates.length} day{uniqueDates.length > 1 ? 's' : ''}</span>
          </div>
        </div>
        
        <div className="mt-2 space-y-1">
          {uniqueDates.map(dateStr => {
            const date = new Date(dateStr);
            const dayName = dayNames[date.getDay()];
            const appointmentsForDate = conflictingAppointments.filter(apt => apt.preferred_date === dateStr);
            
            return (
              <div key={dateStr} className="text-sm text-orange-700">
                • {dayName}, {date.toLocaleDateString()} - {appointmentsForDate.length} appointment{appointmentsForDate.length > 1 ? 's' : ''}
              </div>
            );
          })}
        </div>
      </AlertDescription>
    </Alert>
  );
};