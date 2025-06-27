
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar } from '@/components/ui/calendar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ChevronLeft, ChevronRight, Clock, User, Phone, Calendar as CalendarIcon } from 'lucide-react';
import { Appointment } from '@/types/admin';

interface ExpandedCalendarViewProps {
  appointments: Appointment[];
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  appointmentDates: Date[];
  updateAppointmentStatus: (id: string, status: string) => void;
  onDataRefresh?: () => void;
}

export const ExpandedCalendarView = ({
  appointments,
  selectedDate,
  setSelectedDate,
  appointmentDates,
  updateAppointmentStatus,
  onDataRefresh
}: ExpandedCalendarViewProps) => {
  const [viewMode, setViewMode] = useState<'month' | 'week'>('month');

  // Get appointments for selected date - fix the date comparison
  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    console.log('Looking for appointments on date:', dateStr);
    const filteredAppointments = appointments.filter(apt => {
      console.log('Comparing appointment date:', apt.preferred_date, 'with selected:', dateStr);
      return apt.preferred_date === dateStr;
    });
    console.log('Found appointments for date:', filteredAppointments);
    return filteredAppointments;
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);

  // Generate time slots from 7 AM to 7 PM
  const generateTimeSlots = () => {
    const slots = [];
    for (let hour = 7; hour <= 19; hour++) {
      slots.push(`${hour.toString().padStart(2, '0')}:00`);
    }
    return slots;
  };

  const timeSlots = generateTimeSlots();

  // Get appointments for specific time slot - fix the time matching
  const getAppointmentsForTimeSlot = (timeSlot: string) => {
    console.log('Looking for appointments at time slot:', timeSlot);
    const slotAppointments = selectedDateAppointments.filter(apt => {
      // Convert time slot to match appointment time format
      const appointmentTime = apt.preferred_time;
      console.log('Comparing appointment time:', appointmentTime, 'with slot:', timeSlot);
      
      // Handle both "HH:MM:SS" and "HH:MM" formats
      const appointmentHour = appointmentTime.split(':')[0];
      const slotHour = timeSlot.split(':')[0];
      
      return appointmentHour === slotHour;
    });
    console.log('Found appointments for time slot:', slotAppointments);
    return slotAppointments;
  };

  // Format time for display
  const formatTime = (time: string) => {
    const hour = parseInt(time.split(':')[0]);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:00 ${ampm}`;
  };

  // Get status color
  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'confirmed': return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'completed': return 'bg-green-100 text-green-800 border-green-200';
      case 'cancelled': return 'bg-red-100 text-red-800 border-red-200';
      default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  // Navigate dates
  const navigateDate = (direction: 'prev' | 'next') => {
    const newDate = new Date(selectedDate);
    newDate.setDate(selectedDate.getDate() + (direction === 'next' ? 1 : -1));
    setSelectedDate(newDate);
  };

  return (
    <div className="space-y-6">
      {/* Header Controls */}
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <CalendarIcon className="h-5 w-5" />
              Calendar Management
            </CardTitle>
            <div className="flex items-center gap-2">
              <Select value={viewMode} onValueChange={(value: 'month' | 'week') => setViewMode(value)}>
                <SelectTrigger className="w-32 bg-white text-green-700">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="month">Month View</SelectItem>
                  <SelectItem value="week">Week View</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardHeader>
      </Card>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Calendar Panel */}
        <div className="lg:col-span-1">
          <Card className="border-2 border-green-700">
            <CardHeader className="bg-green-700 text-white">
              <CardTitle>
                {selectedDate.toLocaleDateString('en-US', { 
                  month: 'long', 
                  year: 'numeric' 
                })}
              </CardTitle>
            </CardHeader>
            <CardContent className="p-4">
              <Calendar
                mode="single"
                selected={selectedDate}
                onSelect={(date) => date && setSelectedDate(date)}
                className="w-full"
                modifiers={{
                  hasAppointment: appointmentDates
                }}
                modifiersStyles={{
                  hasAppointment: { 
                    backgroundColor: '#FEE11A', 
                    color: '#000', 
                    fontWeight: 'bold',
                    borderRadius: '50%'
                  }
                }}
              />
              
              {/* Legend */}
              <div className="mt-4 p-3 bg-gray-50 rounded">
                <h4 className="font-medium text-sm mb-2">Legend</h4>
                <div className="flex items-center gap-2 text-xs">
                  <div className="w-4 h-4 bg-yellow-300 rounded-full"></div>
                  <span>Has Appointments</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Day Schedule Panel */}
        <div className="lg:col-span-2">
          <Card className="border-2 border-green-700">
            <CardHeader className="bg-green-700 text-white">
              <div className="flex items-center justify-between">
                <CardTitle className="flex items-center gap-2">
                  <Clock className="h-5 w-5" />
                  {selectedDate.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    month: 'long', 
                    day: 'numeric',
                    year: 'numeric'
                  })}
                </CardTitle>
                <div className="flex items-center gap-2">
                  <Button 
                    variant="ghost" 
                    size="sm" 
                    onClick={() => navigateDate('prev')}
                    className="text-white hover:bg-green-600"
                  >
                    <ChevronLeft className="h-4 w-4" />
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="sm" 
                    onClick={() => navigateDate('next')}
                    className="text-white hover:bg-green-600"
                  >
                    <ChevronRight className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </CardHeader>
            <CardContent className="p-4">
              {/* Daily Summary */}
              <div className="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                  <div>
                    <div className="text-2xl font-bold text-green-600">
                      {selectedDateAppointments.length}
                    </div>
                    <div className="text-sm text-gray-600">Total Appointments</div>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-blue-600">
                      {selectedDateAppointments.filter(apt => apt.status === 'confirmed').length}
                    </div>
                    <div className="text-sm text-gray-600">Confirmed</div>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-yellow-600">
                      {selectedDateAppointments.filter(apt => apt.status === 'pending').length}
                    </div>
                    <div className="text-sm text-gray-600">Pending</div>
                  </div>
                  <div>
                    <div className="text-2xl font-bold text-green-600">
                      {selectedDateAppointments.filter(apt => apt.status === 'completed').length}
                    </div>
                    <div className="text-sm text-gray-600">Completed</div>
                  </div>
                </div>
              </div>

              {/* Debug info - remove this later */}
              <div className="mb-4 p-2 bg-blue-50 rounded text-xs">
                <p>Selected Date: {selectedDate.toISOString().split('T')[0]}</p>
                <p>Total Appointments: {appointments.length}</p>
                <p>Appointments for this date: {selectedDateAppointments.length}</p>
              </div>

              {/* Time Slots */}
              <div className="space-y-2">
                {timeSlots.map((timeSlot) => {
                  const slotAppointments = getAppointmentsForTimeSlot(timeSlot);
                  
                  return (
                    <div
                      key={timeSlot}
                      className={`p-3 rounded-lg border-2 ${
                        slotAppointments.length > 0 
                          ? 'border-green-200 bg-green-50' 
                          : 'border-gray-200 bg-gray-50'
                      }`}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <div className="font-semibold text-green-700">
                          {formatTime(timeSlot)}
                        </div>
                        <Badge variant={slotAppointments.length > 0 ? "default" : "secondary"}>
                          {slotAppointments.length} appointment{slotAppointments.length !== 1 ? 's' : ''}
                        </Badge>
                      </div>

                      {slotAppointments.length > 0 ? (
                        <div className="space-y-2">
                          {slotAppointments.map((appointment) => (
                            <div
                              key={appointment.id}
                              className="p-3 bg-white rounded border border-green-200 shadow-sm"
                            >
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
                })}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};
