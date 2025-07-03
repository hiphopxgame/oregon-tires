import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { UserPlus, AlertTriangle } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { useCustomHours } from '@/hooks/useCustomHours';
import { useEmployees } from '@/hooks/useEmployees';

interface EmployeeScheduleAlertProps {
  selectedDate: Date;
  appointments: Appointment[];
  onAddEmployeeClick?: () => void;
}

export const EmployeeScheduleAlert = ({ 
  selectedDate, 
  appointments, 
  onAddEmployeeClick 
}: EmployeeScheduleAlertProps) => {
  const { getHoursForDate } = useCustomHours();
  const { employees } = useEmployees();
  
  const dateStr = selectedDate.toISOString().split('T')[0];
  const dayHours = getHoursForDate(dateStr);
  
  // If the store is closed, no alert needed
  if (dayHours.is_closed) {
    return null;
  }
  
  const simultaneousBookings = dayHours.simultaneous_bookings || 2;
  
  // Count unique employees assigned to appointments on this date
  const assignedEmployeeIds = new Set(
    appointments
      .filter(apt => apt.assigned_employee_id)
      .map(apt => apt.assigned_employee_id)
  );
  
  const assignedEmployeeCount = assignedEmployeeIds.size;
  const shortfall = simultaneousBookings - assignedEmployeeCount;
  
  // Only show alert if simultaneous bookings exceed assigned employees
  if (shortfall <= 0) {
    return null;
  }
  
  return (
    <Alert className="border-orange-200 bg-orange-50">
      <AlertTriangle className="h-4 w-4 text-orange-600" />
      <AlertDescription className="flex items-center justify-between">
        <div className="flex-1">
          <span className="font-medium text-orange-800">
            Employee shortage detected!
          </span>
          <br />
          <span className="text-sm text-orange-700">
            {simultaneousBookings} simultaneous bookings allowed but only {assignedEmployeeCount} employee{assignedEmployeeCount !== 1 ? 's' : ''} scheduled. 
            Add at least {shortfall} more employee{shortfall !== 1 ? 's' : ''} to meet capacity.
          </span>
        </div>
        {onAddEmployeeClick && (
          <Button
            onClick={onAddEmployeeClick}
            size="sm"
            className="ml-4 bg-orange-600 hover:bg-orange-700 text-white"
          >
            <UserPlus className="h-4 w-4 mr-1" />
            Add Employee
          </Button>
        )}
      </AlertDescription>
    </Alert>
  );
};