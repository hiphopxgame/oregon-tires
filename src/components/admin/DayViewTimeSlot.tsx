
import { Card, CardContent } from '@/components/ui/card';
import { Clock } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { DayViewAppointmentCard } from './DayViewAppointmentCard';

interface TimeSlot {
  time: string;
  appointments: Appointment[];
  hasOverlap: boolean;
  conflictReason?: string;
}

interface DayViewTimeSlotProps {
  slot: TimeSlot;
  getServiceDuration: (service: string) => number;
  checkBusinessHours: (startTime: string, durationHours: number) => boolean;
  formatDuration: (service: string) => string;
  getStatusColor: (status: string) => string;
  capitalizeStatus: (status: string) => string;
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const DayViewTimeSlot = ({
  slot,
  getServiceDuration,
  checkBusinessHours,
  formatDuration,
  getStatusColor,
  capitalizeStatus,
  updateAppointmentStatus
}: DayViewTimeSlotProps) => {
  return (
    <Card 
      className={`border-2 ${slot.hasOverlap ? 'border-red-300 bg-red-50' : 
        slot.appointments.length === 2 ? 'border-yellow-300 bg-yellow-50' : 
        slot.appointments.length === 1 ? 'border-green-300 bg-green-50' : 
        'border-gray-200'}`}
    >
      <CardContent className="p-4">
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-2">
            <Clock className="h-4 w-4 text-gray-500" />
            <span className="font-semibold text-lg">
              {new Date(`2000-01-01T${slot.time}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
              })}
            </span>
          </div>
          <div className="flex items-center gap-2">
            <span className={`px-2 py-1 rounded text-xs font-medium ${
              slot.appointments.length === 0 ? 'bg-gray-100 text-gray-600' :
              slot.appointments.length === 1 ? 'bg-green-100 text-green-700' :
              slot.appointments.length === 2 ? 'bg-yellow-100 text-yellow-700' :
              'bg-red-100 text-red-700'
            }`}>
              {slot.appointments.length === 0 ? 'Available' :
               slot.appointments.length === 1 ? '1 Appointment' :
               slot.appointments.length === 2 ? '2 Appointments (Full)' :
               `${slot.appointments.length} Appointments (OVERBOOKED)`}
            </span>
          </div>
        </div>

        {/* Appointments in this time slot */}
        {slot.appointments.length > 0 && (
          <div className="space-y-3">
            {slot.appointments.map((appointment) => {
              const duration = getServiceDuration(appointment.service);
              const extendsAfterHours = checkBusinessHours(appointment.preferred_time, duration);
              
              return (
                <DayViewAppointmentCard
                  key={appointment.id}
                  appointment={appointment}
                  extendsAfterHours={extendsAfterHours}
                  formatDuration={formatDuration}
                  getStatusColor={getStatusColor}
                  capitalizeStatus={capitalizeStatus}
                  updateAppointmentStatus={updateAppointmentStatus}
                />
              );
            })}
          </div>
        )}

        {/* Empty slot message */}
        {slot.appointments.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            <Clock className="h-8 w-8 mx-auto mb-2 opacity-50" />
            <p className="text-sm">No appointments scheduled</p>
          </div>
        )}
      </CardContent>
    </Card>
  );
};
