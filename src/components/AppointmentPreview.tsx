
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar } from '@/components/ui/calendar';
import { Badge } from '@/components/ui/badge';
import { Clock, AlertCircle, CheckCircle } from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { Appointment } from '@/types/admin';

interface TimeSlotAvailability {
  time: string;
  available: boolean;
  appointmentCount: number;
  maxCapacity: number;
}

export const AppointmentPreview = () => {
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [timeSlots, setTimeSlots] = useState<TimeSlotAvailability[]>([]);
  const [loading, setLoading] = useState(true);

  // Generate time slots from 7 AM to 7 PM
  const generateTimeSlots = () => {
    const slots: TimeSlotAvailability[] = [];
    for (let hour = 7; hour <= 18; hour++) { // Until 6 PM for last appointment
      const timeString = `${hour.toString().padStart(2, '0')}:00`;
      slots.push({
        time: timeString,
        available: true,
        appointmentCount: 0,
        maxCapacity: 2 // Maximum 2 appointments per time slot
      });
    }
    return slots;
  };

  const fetchAppointments = async () => {
    try {
      const { data, error } = await supabase
        .from('oregon_tires_appointments')
        .select('*')
        .order('preferred_date', { ascending: true });

      if (error) throw error;
      setAppointments(data || []);
    } catch (error) {
      console.error('Error fetching appointments:', error);
    } finally {
      setLoading(false);
    }
  };

  const checkAvailability = () => {
    const dateStr = selectedDate.toISOString().split('T')[0];
    const dayAppointments = appointments.filter(apt => apt.preferred_date === dateStr);
    
    const slots = generateTimeSlots();
    
    // Count appointments for each time slot
    dayAppointments.forEach(appointment => {
      const appointmentTime = appointment.preferred_time.substring(0, 5);
      const slot = slots.find(s => s.time === appointmentTime);
      if (slot) {
        slot.appointmentCount++;
      }
    });

    // Update availability based on appointment count
    slots.forEach(slot => {
      slot.available = slot.appointmentCount < slot.maxCapacity;
    });

    setTimeSlots(slots);
  };

  const getAvailabilityStatus = (slot: TimeSlotAvailability) => {
    if (slot.appointmentCount === 0) {
      return { status: 'available', text: 'Available', color: 'bg-green-100 text-green-800' };
    } else if (slot.appointmentCount === 1) {
      return { status: 'limited', text: '1 Spot Left', color: 'bg-yellow-100 text-yellow-800' };
    } else {
      return { status: 'full', text: 'Fully Booked', color: 'bg-red-100 text-red-800' };
    }
  };

  const getDateStatus = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    const dayAppointments = appointments.filter(apt => apt.preferred_date === dateStr);
    
    // Count total available slots for the day
    const totalSlots = generateTimeSlots().length * 2; // 2 appointments per slot
    const bookedSlots = dayAppointments.length;
    
    if (bookedSlots === 0) return 'available';
    if (bookedSlots >= totalSlots) return 'full';
    return 'partial';
  };

  useEffect(() => {
    fetchAppointments();
  }, []);

  useEffect(() => {
    if (appointments.length > 0) {
      checkAvailability();
    }
  }, [selectedDate, appointments]);

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">Loading availability...</div>
      </div>
    );
  }

  const appointmentDates = appointments.map(apt => new Date(apt.preferred_date + 'T00:00:00'));

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="text-center mb-8">
        <h1 className="text-3xl font-bold text-[#007030] mb-2">Check Appointment Availability</h1>
        <p className="text-gray-600">Select a date to see available appointment times</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Calendar */}
        <Card className="border-2 border-[#007030]">
          <CardHeader className="bg-[#007030] text-white">
            <CardTitle className="flex items-center gap-2">
              <Clock className="h-5 w-5" />
              Select Date
            </CardTitle>
          </CardHeader>
          <CardContent className="p-4">
            <Calendar
              mode="single"
              selected={selectedDate}
              onSelect={(date) => date && setSelectedDate(date)}
              className="w-full"
              modifiers={{
                hasAppointments: appointmentDates,
                fullyBooked: appointmentDates.filter(date => getDateStatus(date) === 'full'),
                partiallyBooked: appointmentDates.filter(date => getDateStatus(date) === 'partial')
              }}
              modifiersStyles={{
                hasAppointments: { backgroundColor: '#FEE11A', color: '#000', fontWeight: 'bold' },
                fullyBooked: { backgroundColor: '#ef4444', color: '#fff', fontWeight: 'bold' },
                partiallyBooked: { backgroundColor: '#f59e0b', color: '#fff', fontWeight: 'bold' }
              }}
              disabled={(date) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                return date < today;
              }}
            />
            
            <div className="mt-4 space-y-2">
              <div className="text-sm font-semibold">Legend:</div>
              <div className="flex items-center gap-2 text-xs">
                <div className="w-4 h-4 bg-green-100 border rounded"></div>
                <span>Available</span>
              </div>
              <div className="flex items-center gap-2 text-xs">
                <div className="w-4 h-4 bg-[#FEE11A] border rounded"></div>
                <span>Partially Booked</span>
              </div>
              <div className="flex items-center gap-2 text-xs">
                <div className="w-4 h-4 bg-red-500 border rounded"></div>
                <span>Fully Booked</span>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Time Slots */}
        <Card className="border-2 border-[#007030]">
          <CardHeader className="bg-[#007030] text-white">
            <CardTitle>
              Available Times - {selectedDate.toLocaleDateString('en-US', { 
                weekday: 'long', 
                month: 'long', 
                day: 'numeric' 
              })}
            </CardTitle>
          </CardHeader>
          <CardContent className="p-4">
            <div className="grid gap-3">
              {timeSlots.map((slot) => {
                const status = getAvailabilityStatus(slot);
                return (
                  <div
                    key={slot.time}
                    className="flex items-center justify-between p-3 border rounded-lg"
                  >
                    <div className="flex items-center gap-3">
                      <Clock className="h-4 w-4 text-gray-500" />
                      <span className="font-medium">
                        {slot.time} - {String(parseInt(slot.time.split(':')[0]) + 1).padStart(2, '0')}:00
                      </span>
                    </div>
                    <div className="flex items-center gap-2">
                      {status.status === 'available' && <CheckCircle className="h-4 w-4 text-green-600" />}
                      {status.status === 'limited' && <AlertCircle className="h-4 w-4 text-yellow-600" />}
                      {status.status === 'full' && <AlertCircle className="h-4 w-4 text-red-600" />}
                      <Badge className={status.color}>
                        {status.text}
                      </Badge>
                    </div>
                  </div>
                );
              })}
            </div>

            <div className="mt-6 p-4 bg-blue-50 rounded-lg">
              <h3 className="font-semibold text-blue-900 mb-2">Business Hours & Information</h3>
              <div className="text-sm text-blue-800 space-y-1">
                <p><strong>Hours:</strong> Monday - Saturday 7:00 AM - 7:00 PM</p>
                <p><strong>Sunday:</strong> Closed</p>
                <p><strong>Maximum:</strong> 2 appointments per time slot</p>
                <p><strong>Service Duration:</strong> 1.5 - 3.5 hours depending on service</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};
