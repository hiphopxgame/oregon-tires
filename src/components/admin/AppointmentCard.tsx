
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { User, Phone } from 'lucide-react';
import { Appointment } from '@/types/admin';

interface AppointmentCardProps {
  appointment: Appointment;
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AppointmentCard = ({
  appointment,
  updateAppointmentStatus
}: AppointmentCardProps) => {
  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'completed': return 'bg-green-100 text-green-800 border-green-200';
      case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
      default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  return (
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
          {appointment.message && (
            <div className="text-sm text-gray-600 mb-2">
              <strong>Message:</strong> {appointment.message}
            </div>
          )}
        </div>
        <div className="flex flex-col gap-2">
          <Badge className={getStatusColor(appointment.status)}>
            {appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}
          </Badge>
          <Select
            value={appointment.status}
            onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
          >
            <SelectTrigger className="w-28 h-8 text-xs">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="confirmed">Confirmed</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>
  );
};
