import React from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Users, Calendar } from 'lucide-react';
import { Appointment } from '@/types/admin';

interface Employee {
  id: string;
  name: string;
  is_active: boolean;
}

interface EmployeeScheduleAlertProps {
  appointments: Appointment[];
  employees: Employee[];
  selectedDate: Date;
}

export const EmployeeScheduleAlert: React.FC<EmployeeScheduleAlertProps> = ({
  appointments,
  employees,
  selectedDate
}) => {
  // Get appointments for the selected date
  const dateString = selectedDate.toISOString().split('T')[0];
  const dayAppointments = appointments.filter(apt => apt.preferred_date === dateString);
  
  // Group appointments by time to find simultaneous bookings
  const timeSlots: { [key: string]: Appointment[] } = {};
  dayAppointments.forEach(apt => {
    if (!timeSlots[apt.preferred_time]) {
      timeSlots[apt.preferred_time] = [];
    }
    timeSlots[apt.preferred_time].push(apt);
  });

  // Find time slots with multiple appointments
  const simultaneousBookings = Object.entries(timeSlots)
    .filter(([_, apts]) => apts.length > 1)
    .map(([time, apts]) => ({ time, count: apts.length, appointments: apts }));

  // Find unassigned appointments
  const unassignedAppointments = dayAppointments.filter(apt => !apt.assigned_employee_id);

  // Get scheduled employees for this date (mock data for now - would need real schedule data)
  const scheduledEmployees = employees.filter(emp => emp.is_active);

  if (simultaneousBookings.length === 0 && unassignedAppointments.length === 0) {
    return null;
  }

  return (
    <div className="space-y-4">
      {/* Staffing Alerts */}
      {simultaneousBookings.map(({ time, count, appointments }) => {
        const assignedEmployees = new Set(
          appointments
            .filter(apt => apt.assigned_employee_id)
            .map(apt => apt.assigned_employee_id)
        );
        
        const needsMoreStaff = assignedEmployees.size < count;
        
        return needsMoreStaff ? (
          <Alert key={time} className="border-orange-200 bg-orange-50">
            <AlertTriangle className="h-4 w-4 text-orange-600" />
            <AlertTitle className="text-orange-800">
              Staffing Alert for {time}
            </AlertTitle>
            <AlertDescription className="text-orange-700">
              <div className="flex items-center gap-2 mb-2">
                <Users className="h-4 w-4" />
                <span>
                  {count} simultaneous appointments but only {assignedEmployees.size} employee(s) assigned
                </span>
              </div>
              <div className="flex flex-wrap gap-2">
                {appointments.map(apt => (
                  <Badge key={apt.id} variant="outline" className="text-orange-800 border-orange-300">
                    {apt.first_name} {apt.last_name} - {apt.service}
                  </Badge>
                ))}
              </div>
            </AlertDescription>
          </Alert>
        ) : null;
      })}

      {/* Unassigned Appointments Alert */}
      {unassignedAppointments.length > 0 && (
        <Alert className="border-blue-200 bg-blue-50">
          <Calendar className="h-4 w-4 text-blue-600" />
          <AlertTitle className="text-blue-800">
            Unassigned Appointments ({unassignedAppointments.length})
          </AlertTitle>
          <AlertDescription className="text-blue-700">
            <p className="mb-2">The following appointments need employee assignment:</p>
            <div className="flex flex-wrap gap-2">
              {unassignedAppointments.map(apt => (
                <Badge key={apt.id} variant="outline" className="text-blue-800 border-blue-300">
                  {apt.preferred_time} - {apt.first_name} {apt.last_name}
                </Badge>
              ))}
            </div>
            <p className="mt-2 text-sm">
              Available employees: {scheduledEmployees.map(emp => emp.name).join(', ')}
            </p>
          </AlertDescription>
        </Alert>
      )}
    </div>
  );
};