
import React from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { User, Phone, UserCheck, Play, Square, Clock } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { useEmployees } from '@/hooks/useEmployees';
import { useAppointmentTimer } from '@/hooks/useAppointmentTimer';
import { ScheduleConflictAlert } from './ScheduleConflictAlert';

interface AppointmentCardProps {
  appointment: Appointment;
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
  onAppointmentUpdated?: () => void;
}

export const AppointmentCard = ({
  appointment,
  updateAppointmentStatus,
  updateAppointmentAssignment,
  onAppointmentUpdated
}: AppointmentCardProps) => {
  const { employees } = useEmployees();
  
  const {
    isRunning,
    elapsedTime,
    formattedTime,
    startTimer,
    endTimer
  } = useAppointmentTimer({
    appointmentId: appointment.id,
    onAppointmentUpdated
  });

  // Check if appointment has started or completed times
  const hasStarted = !!appointment.started_at;
  const hasCompleted = !!appointment.completed_at;
  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'new': return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'completed': return 'bg-green-100 text-green-800 border-green-200';
      case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
      default: return 'bg-blue-100 text-blue-800 border-blue-200';
    }
  };

  return (
    <div className="space-y-3">
      {/* Schedule conflict alert */}
      <ScheduleConflictAlert 
        appointment={appointment} 
        employeeName={employees.find(emp => emp.id === appointment.assigned_employee_id)?.name}
      />
      
      <div className="p-3 bg-white rounded border border-green-200 shadow-sm">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <div className="flex items-center gap-2 mb-1">
            <User className="h-4 w-4 text-gray-500" />
            <span className="font-medium">
              {appointment.first_name} {appointment.last_name}
            </span>
          </div>
          <div className="flex items-center gap-2 mb-1 text-sm text-gray-600">
            <Phone className="h-3 w-3" />
            <span>{appointment.phone}</span>
          </div>
          <div className="text-sm text-gray-600 mb-2">
            <strong>Service:</strong> {appointment.service}
          </div>
          <div className="text-sm text-gray-600 mb-2">
            <strong>Time:</strong> {appointment.preferred_time}
          </div>
          {appointment.tire_size && (
            <div className="text-sm text-gray-600 mb-2">
              <strong>Tire Size:</strong> {appointment.tire_size}
            </div>
          )}
          {appointment.license_plate && (
            <div className="text-sm text-gray-600 mb-2">
              <strong>License Plate:</strong> {appointment.license_plate}
            </div>
          )}
          {appointment.vin && (
            <div className="text-sm text-gray-600 mb-2">
              <strong>VIN:</strong> {appointment.vin}
            </div>
          )}
          {appointment.service_location === 'customer-location' && (
            <div className="text-sm text-gray-600 mb-2">
              <strong>Service Location:</strong> {appointment.customer_address}, {appointment.customer_city}, {appointment.customer_state} {appointment.customer_zip}
              {appointment.travel_distance_miles && (
                <div className="mt-1">
                  <strong>Distance:</strong> {appointment.travel_distance_miles} miles
                  {appointment.travel_cost_estimate && (
                    <span className="ml-2"><strong>Travel Cost:</strong> ${appointment.travel_cost_estimate}</span>
                  )}
                </div>
              )}
            </div>
          )}
          {appointment.message && (
            <div className="text-sm text-gray-600 mb-2">
              <strong>Message:</strong> {appointment.message}
            </div>
          )}
          <div className="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <UserCheck className="h-3 w-3" />
            <span><strong>Assigned:</strong> {
              appointment.assigned_employee_id 
                ? (employees?.find(emp => emp.id === appointment.assigned_employee_id)?.name || 'Loading...')
                : 'Unassigned'
            }</span>
          </div>
        </div>
        <div className="flex flex-col gap-2">
          <Badge className={getStatusColor(appointment.status)}>
            {appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}
          </Badge>
          
          {/* Timer Section */}
          <div className="mb-2 p-2 bg-blue-50 rounded border border-blue-200">
            <div className="flex items-center justify-between mb-1">
              <h4 className="font-medium text-xs text-blue-700 flex items-center gap-1">
                <Clock className="h-3 w-3" />
                Timer
              </h4>
              {isRunning && (
                <div className="text-sm font-mono font-bold text-blue-900">
                  {formattedTime}
                </div>
              )}
            </div>
            
            <div className="flex gap-1">
              {!hasStarted && !isRunning && (
                <Button
                  size="sm"
                  onClick={startTimer}
                  className="h-6 px-2 text-xs bg-green-600 hover:bg-green-700 text-white"
                  disabled={appointment.status === 'completed' || appointment.status === 'cancelled'}
                >
                  <Play className="h-2 w-2 mr-1" />
                  Start
                </Button>
              )}
              
              {(hasStarted || isRunning) && !hasCompleted && (
                <Button
                  size="sm"
                  onClick={endTimer}
                  className="h-6 px-2 text-xs bg-red-600 hover:bg-red-700 text-white"
                  disabled={!isRunning && !hasStarted}
                >
                  <Square className="h-2 w-2 mr-1" />
                  End
                </Button>
              )}
              
              {hasCompleted && appointment.actual_duration_minutes && (
                <div className="text-xs text-green-700 font-medium">
                  {appointment.actual_duration_minutes}min
                </div>
              )}
            </div>
            
            {hasStarted && !hasCompleted && !isRunning && (
              <div className="text-xs text-blue-600 mt-1">
                Started: {new Date(appointment.started_at!).toLocaleTimeString()}
              </div>
            )}
          </div>
          
          <Select
            value={appointment.status}
            onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
          >
            <SelectTrigger className="w-28 h-8 text-xs">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="new">New</SelectItem>
              <SelectItem value="confirmed">Confirmed</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
          <Select
            value={appointment.assigned_employee_id || "unassigned"}
            onValueChange={(value) => updateAppointmentAssignment(appointment.id, value === "unassigned" ? null : value)}
          >
            <SelectTrigger className="w-28 h-8 text-xs">
              <SelectValue placeholder="Assign" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="unassigned">Unassigned</SelectItem>
              {employees && employees.length > 0 ? employees.map((employee) => (
                <SelectItem key={employee.id} value={employee.id}>
                  {employee.name} {!employee.is_active && '(Inactive)'}
                </SelectItem>
              )) : (
                <SelectItem value="loading" disabled>Loading employees...</SelectItem>
              )}
            </SelectContent>
          </Select>
        </div>
      </div>
      </div>
    </div>
  );
};
