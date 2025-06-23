
import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';
import { Clock, AlertTriangle, CheckCircle } from 'lucide-react';

interface ScheduleViewStepProps {
  customerInfo: CustomerInfo;
}

interface TimeSlot {
  time: string;
  display: string;
  status: 'available' | 'limited' | 'unavailable';
  conflictCount: number;
  message?: string;
}

export const ScheduleViewStep: React.FC<ScheduleViewStepProps> = ({ customerInfo }) => {
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);

  const serviceDurations: Record<string, number> = {
    'tire-installation': 1.5,
    'tire-repair': 1.5,
    'wheel-alignment': 1.5,
    'brake-service': 2.5,
    'brake-repair': 2.5,
    'oil-change': 3.5,
    'general-maintenance': 3.5,
    'diagnostic': 3.5
  };

  const generateTimeSlots = () => {
    const slots = [];
    for (let hour = 7; hour < 19; hour++) {
      slots.push(`${hour.toString().padStart(2, '0')}:00`);
      slots.push(`${hour.toString().padStart(2, '0')}:30`);
    }
    return slots;
  };

  const formatTimeDisplay = (time: string) => {
    const hour = parseInt(time.split(':')[0]);
    const minute = time.split(':')[1];
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
    return `${displayHour}:${minute} ${ampm}`;
  };

  const timeToMinutes = (timeStr: string) => {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
  };

  const minutesToTime = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
  };

  const checkConsecutiveSlotAvailability = async () => {
    try {
      setLoading(true);
      
      // Fix date parsing - ensure we use the correct date format
      const selectedDate = new Date(customerInfo.preferredDate + 'T00:00:00');
      
      // Check if selected date is Sunday
      if (selectedDate.getDay() === 0) {
        setTimeSlots([]);
        setLoading(false);
        return;
      }

      // Fetch existing appointments for the date
      const { data: appointments, error } = await supabase
        .from('oregon_tires_appointments')
        .select('*')
        .eq('preferred_date', customerInfo.preferredDate)
        .neq('status', 'cancelled');

      if (error) throw error;

      const serviceDuration = serviceDurations[customerInfo.service] || 1.5;
      const serviceDurationMinutes = serviceDuration * 60;
      const slots = generateTimeSlots();
      const availableSlots: TimeSlot[] = [];

      // For each possible starting time slot, check if we can fit the entire service
      slots.forEach(startTime => {
        const startMinutes = timeToMinutes(startTime);
        const endMinutes = startMinutes + serviceDurationMinutes;
        const closingTime = 19 * 60; // 7 PM

        let status: 'available' | 'limited' | 'unavailable' = 'available';
        let conflictCount = 0;
        let message = '';

        // Check if service extends beyond closing time
        if (endMinutes > closingTime) {
          status = 'unavailable';
          message = 'Service would extend beyond closing time (7 PM)';
        } else {
          // Check for conflicts during the entire service duration
          let hasConflict = false;
          let limitedSlots = 0;

          // Check every 30-minute slot that this service would occupy
          for (let checkMinutes = startMinutes; checkMinutes < endMinutes; checkMinutes += 30) {
            let slotConflicts = 0;
            
            appointments?.forEach(apt => {
              const aptTimeStr = apt.preferred_time;
              const aptStartMinutes = timeToMinutes(aptTimeStr.substring(0, 5));
              const aptDuration = serviceDurations[apt.service] || 1.5;
              const aptEndMinutes = aptStartMinutes + (aptDuration * 60);

              // Check if this 30-minute slot overlaps with the appointment
              if (checkMinutes < aptEndMinutes && (checkMinutes + 30) > aptStartMinutes) {
                slotConflicts++;
              }
            });

            if (slotConflicts >= 2) {
              hasConflict = true;
              break;
            } else if (slotConflicts === 1) {
              limitedSlots++;
            }
          }

          if (hasConflict) {
            status = 'unavailable';
            message = 'Time slot conflict - fully booked during service period';
          } else if (limitedSlots > 0) {
            status = 'limited';
            message = `Limited availability - ${limitedSlots} slots with existing appointments`;
          } else {
            message = `Available (${formatTimeDisplay(startTime)} to ${formatTimeDisplay(minutesToTime(endMinutes))})`;
          }
        }

        availableSlots.push({
          time: startTime,
          display: `${formatTimeDisplay(startTime)} to ${formatTimeDisplay(minutesToTime(endMinutes))}`,
          status,
          conflictCount,
          message
        });
      });

      setTimeSlots(availableSlots);
    } catch (error) {
      console.error('Error checking availability:', error);
      toast({
        title: "Error",
        description: "Failed to load schedule availability",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    checkConsecutiveSlotAvailability();
  }, [customerInfo.preferredDate, customerInfo.service]);

  const handleBookAppointment = async () => {
    if (!selectedTime) {
      toast({
        title: "Error",
        description: "Please select a time slot",
        variant: "destructive",
      });
      return;
    }

    setSubmitting(true);
    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .insert({
          first_name: customerInfo.firstName,
          last_name: customerInfo.lastName,
          phone: customerInfo.phone,
          email: customerInfo.email,
          service: customerInfo.service,
          preferred_date: customerInfo.preferredDate,
          preferred_time: selectedTime,
          status: 'pending',
          language: 'english'
        });

      if (error) throw error;

      toast({
        title: "Success!",
        description: "Your appointment has been scheduled successfully.",
        variant: "default",
      });

      // Reset form or redirect
      setSelectedTime('');
      
    } catch (error) {
      console.error('Error booking appointment:', error);
      toast({
        title: "Error",
        description: "Failed to book appointment. Please try again.",
        variant: "destructive",
      });
    } finally {
      setSubmitting(false);
    }
  };

  // Fix date display - ensure proper date parsing
  const selectedDate = new Date(customerInfo.preferredDate + 'T00:00:00');
  const isSunday = selectedDate.getDay() === 0;

  if (loading) {
    return (
      <div className="flex items-center justify-center py-8">
        <p className="text-gray-600">Loading schedule...</p>
      </div>
    );
  }

  if (isSunday) {
    return (
      <div className="text-center py-8">
        <AlertTriangle className="h-12 w-12 text-red-500 mx-auto mb-4" />
        <h3 className="text-xl font-semibold text-red-600 mb-2">Closed on Sundays</h3>
        <p className="text-gray-600">We are closed on Sundays. Please select a different date.</p>
      </div>
    );
  }

  const availableSlots = timeSlots.filter(slot => slot.status === 'available');
  const limitedSlots = timeSlots.filter(slot => slot.status === 'limited');
  const unavailableSlots = timeSlots.filter(slot => slot.status === 'unavailable');

  return (
    <div className="space-y-6">
      {/* Customer summary */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <CheckCircle className="h-5 w-5 text-green-600" />
            Booking Summary
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-2 gap-4 text-sm">
            <div>
              <p><strong>Customer:</strong> {customerInfo.firstName} {customerInfo.lastName}</p>
              <p><strong>Email:</strong> {customerInfo.email}</p>
              <p><strong>Phone:</strong> {customerInfo.phone}</p>
            </div>
            <div>
              <p><strong>Service:</strong> {customerInfo.service.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
              <p><strong>Date:</strong> {selectedDate.toLocaleDateString()}</p>
              <p><strong>Duration:</strong> {serviceDurations[customerInfo.service]} hours</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Schedule display */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Available Time Slots for {selectedDate.toDateString()}
          </CardTitle>
        </CardHeader>
        <CardContent>
          {/* Legend */}
          <div className="flex flex-wrap gap-4 mb-6 text-sm">
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
              <span>Available ({availableSlots.length})</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
              <span>Limited ({limitedSlots.length})</span>
            </div>
            <div className="flex items-center gap-2">
              <div className="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
              <span>Unavailable ({unavailableSlots.length})</span>
            </div>
          </div>

          {/* Available times */}
          {availableSlots.length > 0 && (
            <div className="mb-6">
              <h4 className="font-semibold text-green-700 mb-3">Available Times</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                {availableSlots.map(slot => (
                  <button
                    key={slot.time}
                    onClick={() => setSelectedTime(slot.time)}
                    className={`p-3 rounded-lg border-2 transition-colors text-left ${
                      selectedTime === slot.time
                        ? 'bg-green-600 text-white border-green-600'
                        : 'bg-green-100 border-green-300 text-green-800 hover:bg-green-200'
                    }`}
                  >
                    <div className="font-medium">{slot.display}</div>
                    <div className="text-xs opacity-75">{slot.message}</div>
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Limited times */}
          {limitedSlots.length > 0 && (
            <div className="mb-6">
              <h4 className="font-semibold text-yellow-700 mb-3">Limited Availability</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                {limitedSlots.map(slot => (
                  <button
                    key={slot.time}
                    onClick={() => setSelectedTime(slot.time)}
                    className={`p-3 rounded-lg border-2 transition-colors text-left ${
                      selectedTime === slot.time
                        ? 'bg-yellow-600 text-white border-yellow-600'
                        : 'bg-yellow-100 border-yellow-300 text-yellow-800 hover:bg-yellow-200'
                    }`}
                  >
                    <div className="font-medium">{slot.display}</div>
                    <div className="text-xs opacity-75">{slot.message}</div>
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Unavailable times */}
          {unavailableSlots.length > 0 && (
            <div>
              <h4 className="font-semibold text-red-700 mb-3">Unavailable Times</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                {unavailableSlots.map(slot => (
                  <div
                    key={slot.time}
                    className="p-3 rounded-lg border-2 bg-red-100 border-red-300 text-red-800 opacity-75 cursor-not-allowed"
                    title={slot.message}
                  >
                    <div className="font-medium">{slot.display}</div>
                    <div className="text-xs opacity-75">{slot.message}</div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Book appointment button */}
          {selectedTime && (
            <div className="mt-6 p-4 bg-blue-50 rounded-lg">
              <p className="text-sm mb-3">
                Selected time: <strong>{timeSlots.find(slot => slot.time === selectedTime)?.display}</strong>
              </p>
              <Button 
                onClick={handleBookAppointment}
                disabled={submitting}
                className="bg-[#007030] hover:bg-[#005a26]"
              >
                {submitting ? 'Booking...' : 'Confirm Appointment'}
              </Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
};
