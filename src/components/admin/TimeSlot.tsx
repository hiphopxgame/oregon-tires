
import { Badge } from '@/components/ui/badge';
import { Clock } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { AppointmentCard } from './AppointmentCard';

interface TimeSlotProps {
  timeSlot: string;
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
  onAppointmentUpdated?: () => void;
}

export const TimeSlot = ({
  timeSlot,
  appointments,
  updateAppointmentStatus,
  updateAppointmentAssignment,
  onAppointmentUpdated
}: TimeSlotProps) => {
  const formatTime = (time: string) => {
    const hour = parseInt(time.split(':')[0]);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:00 ${ampm}`;
  };

  return (
    <div
      className={`p-3 rounded-lg border-2 ${
        appointments.length > 0 
          ? 'border-green-200 bg-green-50' 
          : 'border-gray-200 bg-gray-50'
      }`}
    >
      <div className="flex items-center justify-between mb-2">
        <div className="font-semibold text-green-700">
          {formatTime(timeSlot)}
        </div>
        <Badge variant={appointments.length > 0 ? "default" : "secondary"}>
          {appointments.length} appointment{appointments.length !== 1 ? 's' : ''}
        </Badge>
      </div>

      {appointments.length > 0 ? (
        <div className="space-y-2">
          {appointments.map((appointment) => (
            <AppointmentCard
              key={appointment.id}
              appointment={appointment}
              updateAppointmentStatus={updateAppointmentStatus}
              updateAppointmentAssignment={updateAppointmentAssignment}
              onAppointmentUpdated={onAppointmentUpdated}
            />
          ))}
        </div>
      ) : (
        <div className="text-center py-4 text-gray-500">
          <Clock className="h-8 w-8 mx-auto mb-2 opacity-30" />
          <p className="text-sm">No appointments scheduled</p>
        </div>
      )}
    </div>
  );
};
