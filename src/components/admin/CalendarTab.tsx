
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar } from '@/components/ui/calendar';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Appointment } from '@/types/admin';

interface CalendarTabProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const CalendarTab = ({ appointments, updateAppointmentStatus }: CalendarTabProps) => {
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());

  const getStatusBadge = (status: string) => {
    const normalizedStatus = status.toLowerCase();
    const variants = {
      confirmed: { variant: 'default' as const, className: 'bg-blue-500 text-white' },
      completed: { variant: 'default' as const, className: 'bg-green-500 text-white' },
      cancelled: { variant: 'destructive' as const, className: 'bg-red-500 text-white' }
    };

    const variant = variants[normalizedStatus as keyof typeof variants] || variants.confirmed;
    return (
      <Badge variant={variant.variant} className={variant.className}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  // Get appointments for selected date
  const selectedDateStr = selectedDate.toISOString().split('T')[0];
  const dayAppointments = appointments.filter(apt => apt.preferred_date === selectedDateStr);

  // Sort appointments by time
  const sortedDayAppointments = [...dayAppointments].sort((a, b) => 
    a.preferred_time.localeCompare(b.preferred_time)
  );

  // Generate time slots from 7 AM to 7 PM
  const generateTimeSlots = () => {
    const slots = [];
    for (let hour = 7; hour <= 19; hour++) {
      const timeString = `${hour.toString().padStart(2, '0')}:00`;
      const appointmentsAtTime = sortedDayAppointments.filter(apt => 
        apt.preferred_time.startsWith(timeString.substring(0, 2))
      );
      
      slots.push({
        time: timeString,
        displayTime: hour <= 12 ? `${hour}:00 AM` : `${hour - 12}:00 PM`,
        appointments: appointmentsAtTime
      });
    }
    return slots;
  };

  const timeSlots = generateTimeSlots();
  const appointmentDates = appointments.map(apt => new Date(apt.preferred_date + 'T00:00:00'));

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card>
        <CardHeader>
          <CardTitle>Select Date</CardTitle>
          <CardDescription>
            Choose a date to view appointments
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Calendar
            mode="single"
            selected={selectedDate}
            onSelect={(date) => date && setSelectedDate(date)}
            className="w-full"
            modifiers={{
              hasAppointment: appointmentDates
            }}
            modifiersStyles={{
              hasAppointment: { backgroundColor: '#FEE11A', color: '#000', fontWeight: 'bold' }
            }}
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>
            Appointments for {selectedDate.toLocaleDateString()}
          </CardTitle>
          <CardDescription>
            {dayAppointments.length} appointments scheduled
          </CardDescription>
        </CardHeader>
        <CardContent className="max-h-96 overflow-y-auto">
          <div className="space-y-4">
            {timeSlots.map((slot) => (
              <div key={slot.time} className="border-l-4 border-gray-200 pl-4">
                <div className="font-medium text-sm text-gray-600 mb-2">
                  {slot.displayTime}
                </div>
                
                {slot.appointments.length > 0 ? (
                  <div className="space-y-3">
                    {slot.appointments.map((appointment) => (
                      <div key={appointment.id} className="bg-gray-50 rounded-lg p-3 border">
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="font-medium">
                              {appointment.first_name} {appointment.last_name}
                            </div>
                            <div className="text-sm text-gray-600">
                              {appointment.service}
                            </div>
                            <div className="text-sm text-gray-600">
                              {appointment.preferred_time}
                            </div>
                            <div className="text-xs text-gray-500 mt-1">
                              {appointment.email} {appointment.phone && `• ${appointment.phone}`}
                            </div>
                            {appointment.message && (
                              <div className="text-xs text-gray-500 mt-1 italic">
                                "{appointment.message}"
                              </div>
                            )}
                          </div>
                          <div className="ml-4 space-y-2">
                            {getStatusBadge(appointment.status)}
                            <Select
                              value={appointment.status}
                              onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
                            >
                              <SelectTrigger className="w-28 h-8 text-xs">
                                <SelectValue />
                              </SelectTrigger>
                              <SelectContent>
                                <SelectItem value="confirmed">Confirmed</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                              </SelectContent>
                            </Select>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-sm text-gray-400 italic">
                    No appointments
                  </div>
                )}
              </div>
            ))}
          </div>
          
          {dayAppointments.length === 0 && (
            <div className="text-center py-8 text-gray-500">
              No appointments scheduled for this date
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};
