
import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Clock, MapPin, Phone, Mail, AlertTriangle } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useQuery } from '@tanstack/react-query';
import { supabase } from '@/integrations/supabase/client';
import { Appointment } from '@/types/admin';

interface AppointmentPreviewProps {
  onBookAppointment?: (date: Date, time: string, service: string) => void;
}

interface TimeSlotStatus {
  time: string;
  available: boolean;
  reason?: string;
  appointmentCount: number;
}

const AppointmentPreview: React.FC<AppointmentPreviewProps> = ({ onBookAppointment }) => {
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [selectedService, setSelectedService] = useState<string>('');
  const [timeSlots, setTimeSlots] = useState<TimeSlotStatus[]>([]);

  // Service options with durations
  const services = [
    { value: 'tire-installation', label: 'Tire Installation', duration: 1.5 },
    { value: 'tire-repair', label: 'Tire Repair', duration: 1.5 },
    { value: 'wheel-alignment', label: 'Wheel Alignment', duration: 1.5 },
    { value: 'brake-service', label: 'Brake Service', duration: 2.5 },
    { value: 'brake-repair', label: 'Brake Repair', duration: 2.5 },
    { value: 'oil-change', label: 'Oil Change', duration: 3.5 },
    { value: 'general-maintenance', label: 'General Maintenance', duration: 3.5 },
    { value: 'diagnostic', label: 'Diagnostic Service', duration: 3.5 }
  ];

  // Fetch appointments for the selected date
  const { data: appointments = [], refetch } = useQuery({
    queryKey: ['appointments', selectedDate.toISOString().split('T')[0]],
    queryFn: async () => {
      const dateStr = selectedDate.toISOString().split('T')[0];
      const { data, error } = await supabase
        .from('oregon_tires_appointments')
        .select('*')
        .eq('preferred_date', dateStr)
        .neq('status', 'cancelled');

      if (error) throw error;
      return data as Appointment[];
    },
  });

  // Generate time slots from 7 AM to 7 PM
  const generateTimeSlots = () => {
    const slots: string[] = [];
    for (let hour = 7; hour < 19; hour++) {
      slots.push(`${hour.toString().padStart(2, '0')}:00`);
      slots.push(`${hour.toString().padStart(2, '0')}:30`);
    }
    return slots;
  };

  // Convert time string to minutes from start of day
  const timeToMinutes = (timeStr: string) => {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
  };

  // Get service duration
  const getServiceDuration = (serviceType: string) => {
    const service = services.find(s => s.value === serviceType);
    return service ? service.duration : 1.5;
  };

  // Check if a time slot conflicts with existing appointments or business hours
  const checkTimeSlotAvailability = (timeSlot: string, serviceDuration: number) => {
    const slotStartMinutes = timeToMinutes(timeSlot);
    const slotEndMinutes = slotStartMinutes + (serviceDuration * 60);
    const closingTime = 19 * 60; // 7 PM in minutes

    // Check if service extends beyond business hours
    if (slotEndMinutes > closingTime) {
      const overtimeHours = Math.round((slotEndMinutes - closingTime) / 60 * 10) / 10;
      return {
        available: false,
        reason: `Service would extend ${overtimeHours} hours beyond closing time (7 PM)`,
        appointmentCount: 0
      };
    }

    // Count overlapping appointments
    let overlappingCount = 0;
    const conflictingAppointments: string[] = [];

    appointments.forEach(apt => {
      const aptStartMinutes = timeToMinutes(apt.preferred_time.substring(0, 5));
      const aptDuration = getServiceDuration(apt.service);
      const aptEndMinutes = aptStartMinutes + (aptDuration * 60);

      // Check if appointments overlap
      if (slotStartMinutes < aptEndMinutes && slotEndMinutes > aptStartMinutes) {
        overlappingCount++;
        conflictingAppointments.push(`${apt.first_name} ${apt.last_name} (${apt.service})`);
      }
    });

    // Maximum 2 simultaneous appointments allowed
    if (overlappingCount >= 2) {
      return {
        available: false,
        reason: `Time slot fully booked (${overlappingCount} appointments already scheduled)`,
        appointmentCount: overlappingCount
      };
    }

    return {
      available: true,
      appointmentCount: overlappingCount
    };
  };

  // Update time slots when service or date changes
  useEffect(() => {
    if (!selectedService) {
      setTimeSlots([]);
      return;
    }

    const serviceDuration = getServiceDuration(selectedService);
    const allTimeSlots = generateTimeSlots();
    
    const updatedSlots: TimeSlotStatus[] = allTimeSlots.map(time => {
      const status = checkTimeSlotAvailability(time, serviceDuration);
      
      return {
        time,
        available: status.available,
        reason: status.reason,
        appointmentCount: status.appointmentCount
      };
    });

    setTimeSlots(updatedSlots);
  }, [selectedService, appointments, selectedDate]);

  const handleBooking = (time: string) => {
    if (onBookAppointment && selectedService) {
      onBookAppointment(selectedDate, time, selectedService);
    }
  };

  const formatTime = (time: string) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
  };

  const getSlotStatusColor = (slot: TimeSlotStatus) => {
    if (!slot.available) return 'bg-red-100 text-red-800 cursor-not-allowed';
    if (slot.appointmentCount === 1) return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200';
    return 'bg-green-100 text-green-800 hover:bg-green-200';
  };

  const getSlotStatusText = (slot: TimeSlotStatus) => {
    if (!slot.available) return 'Unavailable';
    if (slot.appointmentCount === 1) return 'Limited (1 slot left)';
    return 'Available';
  };

  const selectedServiceInfo = services.find(s => s.value === selectedService);

  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="text-center space-y-2">
        <h1 className="text-3xl font-bold text-green-700">Check Appointment Availability</h1>
        <p className="text-gray-600">Select your service and preferred date to see available time slots</p>
      </div>

      {/* Business Info */}
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle className="flex items-center gap-2">
            <MapPin className="h-5 w-5" />
            Oregon Tires Auto Care
          </CardTitle>
        </CardHeader>
        <CardContent className="p-4">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="flex items-center gap-2">
              <Phone className="h-4 w-4 text-green-600" />
              <span>(503) 555-0123</span>
            </div>
            <div className="flex items-center gap-2">
              <Mail className="h-4 w-4 text-green-600" />
              <span>info@oregontires.com</span>
            </div>
            <div className="flex items-center gap-2">
              <Clock className="h-4 w-4 text-green-600" />
              <span>Mon-Sat: 7AM-7PM</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Service Selection */}
      <Card>
        <CardHeader>
          <CardTitle>Step 1: Select Your Service</CardTitle>
        </CardHeader>
        <CardContent>
          <Select value={selectedService} onValueChange={setSelectedService}>
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Choose a service..." />
            </SelectTrigger>
            <SelectContent>
              {services.map(service => (
                <SelectItem key={service.value} value={service.value}>
                  {service.label} ({service.duration}h)
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          
          {selectedServiceInfo && (
            <div className="mt-3 p-3 bg-blue-50 rounded-lg">
              <p className="text-sm text-blue-800">
                <strong>{selectedServiceInfo.label}</strong> - Estimated duration: {selectedServiceInfo.duration} hours
              </p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Date Selection */}
      {selectedService && (
        <Card>
          <CardHeader>
            <CardTitle>Step 2: Select Date</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex justify-center">
              <Calendar
                mode="single"
                selected={selectedDate}
                onSelect={setSelectedDate}
                disabled={(date) => {
                  const today = new Date();
                  today.setHours(0, 0, 0, 0);
                  return date < today || date.getDay() === 0; // Disable past dates and Sundays
                }}
                className="rounded-md border"
              />
            </div>
          </CardContent>
        </Card>
      )}

      {/* Time Slots */}
      {selectedService && selectedDate && (
        <Card>
          <CardHeader>
            <CardTitle>
              Step 3: Available Time Slots for {selectedDate.toLocaleDateString()}
            </CardTitle>
          </CardHeader>
          <CardContent>
            {timeSlots.length === 0 ? (
              <p className="text-center text-gray-500">Select a service to see available time slots</p>
            ) : (
              <>
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                  {timeSlots.map((slot) => (
                    <div key={slot.time} className="relative">
                      <Button
                        variant="outline"
                        className={`w-full h-16 flex flex-col items-center justify-center text-xs ${getSlotStatusColor(slot)}`}
                        disabled={!slot.available}
                        onClick={() => slot.available && handleBooking(slot.time)}
                      >
                        <div className="font-semibold">{formatTime(slot.time)}</div>
                        <div className="text-xs opacity-75">{getSlotStatusText(slot)}</div>
                      </Button>
                      
                      {slot.reason && (
                        <div className="absolute top-full left-0 right-0 mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700 z-10 opacity-0 hover:opacity-100 transition-opacity">
                          {slot.reason}
                        </div>
                      )}
                    </div>
                  ))}
                </div>

                {/* Legend */}
                <div className="mt-6 flex flex-wrap gap-4 justify-center">
                  <div className="flex items-center gap-2">
                    <div className="w-4 h-4 bg-green-100 border border-green-200 rounded"></div>
                    <span className="text-sm">Available (2 slots)</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-4 h-4 bg-yellow-100 border border-yellow-200 rounded"></div>
                    <span className="text-sm">Limited (1 slot left)</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-4 h-4 bg-red-100 border border-red-200 rounded"></div>
                    <span className="text-sm">Unavailable</span>
                  </div>
                </div>

                {/* Conflict Warning */}
                {timeSlots.some(slot => !slot.available && slot.reason?.includes('beyond closing time')) && (
                  <Alert className="mt-4 border-orange-200 bg-orange-50">
                    <AlertTriangle className="h-4 w-4 text-orange-600" />
                    <AlertDescription className="text-orange-800">
                      Some time slots are unavailable because the selected service would extend beyond our closing time (7 PM). 
                      Consider booking an earlier time slot or choosing a shorter service.
                    </AlertDescription>
                  </Alert>
                )}
              </>
            )}
          </CardContent>
        </Card>
      )}

      {/* Instructions */}
      <Card className="border-2 border-blue-200 bg-blue-50">
        <CardContent className="p-4">
          <h3 className="font-semibold mb-2 text-blue-800">Booking Instructions:</h3>
          <ul className="text-sm text-blue-700 space-y-1">
            <li>• Maximum 2 appointments can be scheduled simultaneously</li>
            <li>• All services must be completed by 7 PM closing time</li>
            <li>• Service durations: Tire services (1.5h), Brake services (2.5h), Other services (3.5h)</li>
            <li>• We are closed on Sundays</li>
          </ul>
        </CardContent>
      </Card>
    </div>
  );
};

export default AppointmentPreview;
