import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { User, Clock, Wrench, CheckCircle } from 'lucide-react';
import { Appointment } from '@/types/admin';

interface Employee {
  id: string;
  name: string;
  is_active: boolean;
}

interface EmployeeAppointmentsProps {
  appointments: Appointment[];
  employees: Employee[];
  selectedDate: Date;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
}

export const EmployeeAppointments: React.FC<EmployeeAppointmentsProps> = ({
  appointments,
  employees,
  selectedDate,
  updateAppointmentAssignment
}) => {
  const dateString = selectedDate.toISOString().split('T')[0];
  const dayAppointments = appointments.filter(apt => apt.preferred_date === dateString);
  const unassignedAppointments = dayAppointments.filter(apt => !apt.assigned_employee_id);
  
  const activeEmployees = employees.filter(emp => emp.is_active);

  const handleQuickAssign = () => {
    // Auto-assign unassigned appointments to available employees
    unassignedAppointments.forEach((apt, index) => {
      const employeeIndex = index % activeEmployees.length;
      const employee = activeEmployees[employeeIndex];
      if (employee) {
        updateAppointmentAssignment(apt.id, employee.id);
      }
    });
  };

  if (dayAppointments.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Employee Assignments
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-gray-500">No appointments scheduled for this date.</p>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2 justify-between">
          <div className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Employee Assignments ({dayAppointments.length} appointments)
          </div>
          {unassignedAppointments.length > 0 && (
            <Button onClick={handleQuickAssign} size="sm" variant="outline">
              <CheckCircle className="h-4 w-4 mr-2" />
              Quick Assign All
            </Button>
          )}
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {dayAppointments
          .sort((a, b) => a.preferred_time.localeCompare(b.preferred_time))
          .map(appointment => (
            <div
              key={appointment.id}
              className={`p-4 border rounded-lg ${
                !appointment.assigned_employee_id 
                  ? 'border-orange-200 bg-orange-50' 
                  : 'border-green-200 bg-green-50'
              }`}
            >
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2">
                    <Clock className="h-4 w-4 text-gray-500" />
                    <span className="font-medium">{appointment.preferred_time}</span>
                    <Badge variant={appointment.assigned_employee_id ? "default" : "destructive"}>
                      {appointment.assigned_employee_id ? "Assigned" : "Unassigned"}
                    </Badge>
                  </div>
                  
                  <div className="text-sm space-y-1">
                    <div className="flex items-center gap-2">
                      <User className="h-3 w-3 text-gray-400" />
                      <span>{appointment.first_name} {appointment.last_name}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Wrench className="h-3 w-3 text-gray-400" />
                      <span>{appointment.service}</span>
                    </div>
                  </div>
                </div>
                
                <div className="ml-4">
                  <Select
                    value={appointment.assigned_employee_id || "unassigned"}
                    onValueChange={(value) => 
                      updateAppointmentAssignment(
                        appointment.id, 
                        value === "unassigned" ? null : value
                      )
                    }
                  >
                    <SelectTrigger className="w-40">
                      <SelectValue placeholder="Assign employee" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="unassigned">Unassigned</SelectItem>
                      {activeEmployees.map(employee => (
                        <SelectItem key={employee.id} value={employee.id}>
                          {employee.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </div>
          ))}
      </CardContent>
    </Card>
  );
};