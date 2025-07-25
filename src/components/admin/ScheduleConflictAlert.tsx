import React from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Calendar, Clock } from 'lucide-react';
import { useEmployeeSchedules } from '@/hooks/useEmployeeSchedules';
import { Appointment } from '@/types/admin';
import { format } from 'date-fns';

interface ScheduleConflictAlertProps {
  appointment: Appointment;
  employeeName?: string;
}

export const ScheduleConflictAlert = ({ appointment, employeeName }: ScheduleConflictAlertProps) => {
  const { isEmployeeScheduled, getEmployeeScheduleForDate, employeesWithSchedules } = useEmployeeSchedules();

  if (!appointment.assigned_employee_id) return null;

  const appointmentDate = new Date(appointment.preferred_date + 'T00:00:00');
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  // Only show conflicts for future appointments
  if (appointmentDate < today) return null;
  
  const isScheduled = isEmployeeScheduled(appointment.assigned_employee_id, appointmentDate);
  
  // Debug logging
  const employee = employeesWithSchedules.find(emp => emp.id === appointment.assigned_employee_id);
  const dateStr = appointmentDate.toISOString().split('T')[0];
  console.log('ScheduleConflictAlert Debug:', {
    employeeId: appointment.assigned_employee_id,
    employeeName,
    appointmentDate: dateStr,
    employee: employee ? { id: employee.id, name: employee.name, schedulesCount: employee.schedules.length } : null,
    schedules: employee?.schedules || [],
    isScheduled,
    lookingForDate: dateStr
  });
  
  if (isScheduled) return null;

  const schedule = getEmployeeScheduleForDate(appointment.assigned_employee_id, appointmentDate);
  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const dayName = dayNames[appointmentDate.getDay()];

  return (
    <Alert className="border-orange-200 bg-orange-50">
      <AlertTriangle className="h-4 w-4 text-orange-600" />
      <AlertDescription className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <span className="text-orange-800">
            <strong>{employeeName || 'Employee'}</strong> is not scheduled for {dayName}
          </span>
          <Badge variant="outline" className="text-orange-700 border-orange-300">
            Schedule Conflict
          </Badge>
        </div>
        <div className="flex items-center gap-1 text-sm text-orange-700">
          <Clock className="h-3 w-3" />
          <span>Appointment at {appointment.preferred_time}</span>
        </div>
      </AlertDescription>
    </Alert>
  );
};