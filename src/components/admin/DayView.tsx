
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Input } from '@/components/ui/input';
import { AlertTriangle, Clock, User, Edit2, Save, X } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';

interface DayViewProps {
  appointments: Appointment[];
  selectedDate: Date;
  updateAppointmentStatus: (id: string, status: string) => void;
}

interface TimeSlot {
  time: string;
  appointments: Appointment[];
  hasOverlap: boolean;
  conflictReason?: string;
}

interface EditingAppointment {
  id: string;
  preferred_date: string;
  preferred_time: string;
}

export const DayView = ({ appointments, selectedDate, updateAppointmentStatus }: DayViewProps) => {
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [overlapWarnings, setOverlapWarnings] = useState<string[]>([]);
  const [editingAppointment, setEditingAppointment] = useState<EditingAppointment | null>(null);
  const { toast } = useToast();

  // Get service duration in hours
  const getServiceDuration = (service: string) => {
    const serviceType = service.toLowerCase();
    if (serviceType.includes('tire')) {
      return 1.5; // 1.5 hours for tire services
    } else if (serviceType.includes('brake')) {
      return 2.5; // 2.5 hours for brake services
    } else {
      return 3.5; // 3.5 hours for everything else
    }
  };

  // Convert time string to minutes from start of day
  const timeToMinutes = (timeStr: string) => {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
  };

  // Convert minutes to time string
  const minutesToTime = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
  };

  // Check if appointment extends beyond 7 PM
  const checkBusinessHours = (startTime: string, durationHours: number) => {
    const startMinutes = timeToMinutes(startTime);
    const endMinutes = startMinutes + (durationHours * 60);
    const closingTime = 19 * 60; // 7 PM in minutes
    return endMinutes > closingTime;
  };

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

  // Update appointment date and time
  const updateAppointmentDateTime = async (appointmentId: string, newDate: string, newTime: string) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({
          preferred_date: newDate,
          preferred_time: newTime
        })
        .eq('id', appointmentId);

      if (error) throw error;

      toast({
        title: "Appointment Updated",
        description: "Date and time have been successfully updated.",
      });

      // Refresh the page or update local state
      window.location.reload();
    } catch (error) {
      console.error('Error updating appointment:', error);
      toast({
        title: "Error",
        description: "Failed to update appointment date/time",
        variant: "destructive",
      });
    }
  };

  const startEditing = (appointment: Appointment) => {
    setEditingAppointment({
      id: appointment.id,
      preferred_date: appointment.preferred_date,
      preferred_time: appointment.preferred_time
    });
  };

  const cancelEditing = () => {
    setEditingAppointment(null);
  };

  const saveEditing = () => {
    if (editingAppointment) {
      updateAppointmentDateTime(
        editingAppointment.id,
        editingAppointment.preferred_date,
        editingAppointment.preferred_time
      );
      setEditingAppointment(null);
    }
  };

  // Check for appointment overlaps and conflicts
  const checkOverlaps = (dayAppointments: Appointment[]) => {
    const warnings: string[] = [];
    const slots = generateTimeSlots();

    // Sort appointments by time
    const sortedAppointments = dayAppointments.sort((a, b) => 
      a.preferred_time.localeCompare(b.preferred_time)
    );

    // Create appointment intervals with durations
    const appointmentIntervals = sortedAppointments.map(apt => {
      const startMinutes = timeToMinutes(apt.preferred_time.substring(0, 5));
      const duration = getServiceDuration(apt.service);
      const endMinutes = startMinutes + (duration * 60);
      
      return {
        appointment: apt,
        startMinutes,
        endMinutes,
        duration
      };
    });

    // Check for business hour violations
    appointmentIntervals.forEach(interval => {
      const closingTime = 19 * 60; // 7 PM
      if (interval.endMinutes > closingTime) {
        const overage = Math.round((interval.endMinutes - closingTime) / 60 * 10) / 10;
        warnings.push(`${interval.appointment.first_name} ${interval.appointment.last_name}'s ${interval.appointment.service} appointment at ${interval.appointment.preferred_time} will extend ${overage} hours beyond closing time (7 PM)`);
      }
    });

    // Check for overlaps (max 2 simultaneous appointments)
    for (let i = 0; i < appointmentIntervals.length; i++) {
      let overlapping = 0;
      const currentInterval = appointmentIntervals[i];
      
      for (let j = 0; j < appointmentIntervals.length; j++) {
        if (i === j) continue;
        
        const otherInterval = appointmentIntervals[j];
        
        // Check if intervals overlap
        if (currentInterval.startMinutes < otherInterval.endMinutes && 
            currentInterval.endMinutes > otherInterval.startMinutes) {
          overlapping++;
        }
      }
      
      if (overlapping >= 2) {
        warnings.push(`${currentInterval.appointment.first_name} ${currentInterval.appointment.last_name}'s appointment at ${currentInterval.appointment.preferred_time} conflicts with 2+ other appointments (maximum 2 simultaneous allowed)`);
      }
    }

    // Map appointments to their starting time slots
    sortedAppointments.forEach(appointment => {
      const appointmentTime = appointment.preferred_time.substring(0, 5);
      const slot = slots.find(s => s.time === appointmentTime);
      if (slot) {
        slot.appointments.push(appointment);
      }
    });

    // Mark slots with too many appointments
    slots.forEach(slot => {
      if (slot.appointments.length > 2) {
        slot.hasOverlap = true;
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

  const formatDuration = (service: string) => {
    const duration = getServiceDuration(service);
    return `${duration}h`;
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

      {/* Service Duration Legend */}
      <Card className="border-2 border-blue-200 bg-blue-50">
        <CardContent className="p-4">
          <h3 className="font-semibold mb-2">Service Durations:</h3>
          <div className="grid grid-cols-3 gap-4 text-sm">
            <div><strong>Tire Services:</strong> 1.5 hours</div>
            <div><strong>Brake Services:</strong> 2.5 hours</div>
            <div><strong>Other Services:</strong> 3.5 hours</div>
          </div>
          <p className="text-xs text-gray-600 mt-2">Business hours: 7 AM - 7 PM</p>
        </CardContent>
      </Card>

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
                  {slot.appointments.map((appointment) => {
                    const duration = getServiceDuration(appointment.service);
                    const extendsAfterHours = checkBusinessHours(appointment.preferred_time, duration);
                    const isEditing = editingAppointment?.id === appointment.id;
                    
                    return (
                      <div key={appointment.id} className={`border rounded-lg p-3 ${extendsAfterHours ? 'bg-red-50 border-red-200' : 'bg-white'}`}>
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
                            <p className="text-xs text-gray-500">Duration: {formatDuration(appointment.service)}</p>
                            {extendsAfterHours && (
                              <p className="text-xs text-red-600 font-medium">⚠️ Extends past 7 PM</p>
                            )}
                            <div className={`inline-block px-2 py-1 rounded text-xs font-medium border ${getStatusColor(appointment.status)}`}>
                              {capitalizeStatus(appointment.status)}
                            </div>
                          </div>
                        </div>

                        {/* Date and Time Editing Section */}
                        <div className="mb-3 p-3 bg-gray-50 rounded border">
                          <div className="flex items-center justify-between mb-2">
                            <h4 className="font-medium text-sm text-gray-700">Appointment Date & Time</h4>
                            {!isEditing ? (
                              <Button
                                size="sm"
                                variant="outline"
                                onClick={() => startEditing(appointment)}
                                className="h-7 px-2"
                              >
                                <Edit2 className="h-3 w-3 mr-1" />
                                Edit
                              </Button>
                            ) : (
                              <div className="flex gap-1">
                                <Button
                                  size="sm"
                                  onClick={saveEditing}
                                  className="h-7 px-2 bg-green-600 hover:bg-green-700"
                                >
                                  <Save className="h-3 w-3 mr-1" />
                                  Save
                                </Button>
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={cancelEditing}
                                  className="h-7 px-2"
                                >
                                  <X className="h-3 w-3 mr-1" />
                                  Cancel
                                </Button>
                              </div>
                            )}
                          </div>
                          
                          {isEditing ? (
                            <div className="grid grid-cols-2 gap-2">
                              <div>
                                <label className="text-xs text-gray-600 block mb-1">Date</label>
                                <Input
                                  type="date"
                                  value={editingAppointment.preferred_date}
                                  onChange={(e) => setEditingAppointment(prev => prev ? {...prev, preferred_date: e.target.value} : null)}
                                  className="h-8 text-xs"
                                />
                              </div>
                              <div>
                                <label className="text-xs text-gray-600 block mb-1">Time</label>
                                <Input
                                  type="time"
                                  value={editingAppointment.preferred_time}
                                  onChange={(e) => setEditingAppointment(prev => prev ? {...prev, preferred_time: e.target.value} : null)}
                                  className="h-8 text-xs"
                                />
                              </div>
                            </div>
                          ) : (
                            <div className="grid grid-cols-2 gap-2 text-sm">
                              <div>
                                <span className="text-gray-600">Date:</span>
                                <div className="font-medium">{appointment.preferred_date}</div>
                              </div>
                              <div>
                                <span className="text-gray-600">Time:</span>
                                <div className="font-medium">{appointment.preferred_time}</div>
                              </div>
                            </div>
                          )}
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
