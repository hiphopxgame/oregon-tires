
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertTriangle, Clock, User } from 'lucide-react';
import { Appointment } from '@/types/admin';

interface DayViewProps {
  appointments: Appointment[];
  selectedDate: Date;
  updateAppointmentStatus: (id: string, status: string) => void;
}

interface TimeSlot {
  time: string;
  appointments: Appointment[];
  hasOverlap: boolean;
}

export const DayView = ({ appointments, selectedDate, updateAppointmentStatus }: DayViewProps) => {
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [overlapWarnings, setOverlapWarnings] = useState<string[]>([]);

  // Generate time slots from 7 AM to 7 PM
  const generateTimeSlots = () => {
    const slots: TimeSlot[] = [];
    for (let hour = 7; hour <= 19; hour++) {
      const timeString = `${hour.toString().padStart(2, '0')}:00`;
      slots.push({
        time: timeString,
        appointments: [],
        hasOverlap: false
      });
    }
    return slots;
  };

  // Check for appointment overlaps and conflicts
  const checkOverlaps = (dayAppointments: Appointment[]) => {
    const warnings: string[] = [];
    const slots = generateTimeSlots();

    // Sort appointments by time
    const sortedAppointments = dayAppointments.sort((a, b) => 
      a.preferred_time.localeCompare(b.preferred_time)
    );

    // Map appointments to time slots and check for overlaps
    sortedAppointments.forEach(appointment => {
      const appointmentTime = appointment.preferred_time.substring(0, 5); // Get HH:MM format
      const slot = slots.find(s => s.time === appointmentTime);
      if (slot) {
        slot.appointments.push(appointment);
      }
    });

    // Check each slot for overlaps
    slots.forEach(slot => {
      if (slot.appointments.length > 2) {
        slot.hasOverlap = true;
        warnings.push(`${slot.time}: ${slot.appointments.length} appointments scheduled (maximum 2 allowed)`);
      } else if (slot.appointments.length === 2) {
        warnings.push(`${slot.time}: 2 appointments scheduled (at capacity)`);
      }

      // Check 30-minute buffer rule
      if (slot.appointments.length > 0) {
        const currentHour = parseInt(slot.time.split(':')[0]);
        const nextSlot = slots.find(s => s.time === `${(currentHour + 1).toString().padStart(2, '0')}:00`);
        
        if (nextSlot && nextSlot.appointments.length > 0) {
          warnings.push(`Warning: Appointments at ${slot.time} may not have adequate 30-minute buffer before ${nextSlot.time}`);
        }
      }
    });

    setTimeSlots(slots);
    setOverlapWarnings(warnings);
  };

  useEffect(() => {
    const dateStr = selectedDate.toISOString().split('T')[0];
    const dayAppointments = appointments.filter(apt => apt.preferred_date === dateStr);
    checkOverlaps(dayAppointments);
  }, [appointments, selectedDate]);

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'new': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'priority': return 'bg-red-100 text-red-800 border-red-200';
      case 'completed': return 'bg-green-100 text-green-800 border-green-200';
      default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  const capitalizeStatus = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
  };

  return (
    <div className="space-y-6">
      {/* Date Header */}
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Day View - {selectedDate.toLocaleDateString('en-US', { 
              weekday: 'long', 
              year: 'numeric', 
              month: 'long', 
              day: 'numeric' 
            })}
          </CardTitle>
        </CardHeader>
      </Card>

      {/* Overlap Warnings */}
      {overlapWarnings.length > 0 && (
        <Alert variant="destructive" className="border-red-200 bg-red-50">
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription>
            <div className="font-semibold mb-2">Scheduling Conflicts Detected:</div>
            <ul className="list-disc list-inside space-y-1">
              {overlapWarnings.map((warning, index) => (
                <li key={index} className="text-sm">{warning}</li>
              ))}
            </ul>
          </AlertDescription>
        </Alert>
      )}

      {/* Time Slots Grid */}
      <div className="grid gap-4">
        {timeSlots.map((slot) => (
          <Card 
            key={slot.time} 
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
                  {slot.appointments.map((appointment) => (
                    <div key={appointment.id} className="border rounded-lg p-3 bg-white">
                      <div className="flex items-start justify-between mb-2">
                        <div className="flex items-center gap-2">
                          <User className="h-4 w-4 text-gray-500" />
                          <div>
                            <p className="font-medium text-gray-900">
                              {appointment.first_name} {appointment.last_name}
                            </p>
                            <p className="text-sm text-gray-600">{appointment.phone}</p>
                            <p className="text-sm text-gray-600">{appointment.email}</p>
                          </div>
                        </div>
                        <div className="text-right">
                          <p className="font-medium text-sm">{appointment.service}</p>
                          <div className={`inline-block px-2 py-1 rounded text-xs font-medium border ${getStatusColor(appointment.status)}`}>
                            {capitalizeStatus(appointment.status)}
                          </div>
                        </div>
                      </div>

                      {appointment.message && (
                        <p className="text-sm text-gray-600 mb-2 italic">"{appointment.message}"</p>
                      )}

                      <div className="flex items-center gap-2">
                        <span className="text-xs text-gray-500">Status:</span>
                        <Select
                          value={capitalizeStatus(appointment.status)}
                          onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
                        >
                          <SelectTrigger className="w-32 h-8 text-xs">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="New">New</SelectItem>
                            <SelectItem value="Priority">Priority</SelectItem>
                            <SelectItem value="Completed">Completed</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                  ))}
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
        ))}
      </div>

      {/* Summary */}
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle>Daily Summary</CardTitle>
        </CardHeader>
        <CardContent className="p-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
              <div className="text-2xl font-bold text-green-600">
                {timeSlots.reduce((sum, slot) => sum + slot.appointments.length, 0)}
              </div>
              <div className="text-sm text-gray-600">Total Appointments</div>
            </div>
            <div>
              <div className="text-2xl font-bold text-yellow-600">
                {timeSlots.filter(slot => slot.appointments.length === 2).length}
              </div>
              <div className="text-sm text-gray-600">Full Time Slots</div>
            </div>
            <div>
              <div className="text-2xl font-bold text-red-600">
                {timeSlots.filter(slot => slot.hasOverlap).length}
              </div>
              <div className="text-sm text-gray-600">Overbooked Slots</div>
            </div>
            <div>
              <div className="text-2xl font-bold text-gray-600">
                {timeSlots.filter(slot => slot.appointments.length === 0).length}
              </div>
              <div className="text-sm text-gray-600">Available Slots</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
