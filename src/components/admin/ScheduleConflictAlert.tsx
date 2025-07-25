import React from 'react';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Calendar, Clock } from 'lucide-react';
import { useEmployeeSchedules } from '@/hooks/useEmployeeSchedules';
import { Appointment } from '@/types/admin';

interface ScheduleConflictAlertProps {
  appointment: Appointment;
  employeeName?: string;
}

export const ScheduleConflictAlert = ({ appointment, employeeName }: ScheduleConflictAlertProps) => {
  const { isEmployeeScheduled, getEmployeeScheduleForDate } = useEmployeeSchedules();

  if (!appointment.assigned_employee_id) return null;

  const appointmentDate = new Date(appointment.preferred_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  // Only show conflicts for future appointments
  if (appointmentDate < today) return null;
  
  const isScheduled = isEmployeeScheduled(appointment.assigned_employee_id, appointmentDate);
  
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
          <Calendar className="h-3 w-3" />
          <span>Needs scheduling</span>
        </div>
      </AlertDescription>
    </Alert>
  );
};