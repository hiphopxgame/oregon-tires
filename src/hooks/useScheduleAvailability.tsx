import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';
import { useCustomHours } from '@/hooks/useCustomHours';

interface TimeSlot {
  time: string;
  display: string;
  status: 'available' | 'unavailable';
  conflictCount: number;
  message?: string;
}

interface UseScheduleAvailabilityProps {
  preferredDate: string;
  service: string;
}

export const useScheduleAvailability = ({ preferredDate, service }: UseScheduleAvailabilityProps) => {
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [loading, setLoading] = useState(true);
  const { getHoursForDate } = useCustomHours();

  const serviceDurations: Record<string, number> = {
    'new-tires': 2,
    'used-tires': 2,
    'mount-and-balance-tires': 2,
    'tire-repair': 1,
    'oil-change': 1.25,
    'front-or-back-brake-change': 2,
    'full-brake-change': 3.5,
    'tuneup': 5,
    'alignment': 2,
    'mechanical-inspection-and-estimate': 2.5
  };

  const generateTimeSlots = (startHour: number = 7, endHour: number = 19) => {
    const slots = [];
    for (let hour = startHour; hour < endHour; hour++) {
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

  const isPastTime = (date: string, time: string) => {
    // Create a date object for the appointment time in Pacific Time
    const appointmentDateTime = new Date(`${date}T${time}:00-08:00`); // PST is UTC-8
    const now = new Date();
    
    return appointmentDateTime < now;
  };

  const checkConsecutiveSlotAvailability = async () => {
    try {
      setLoading(true);
      
      // Get hours and simultaneous booking capacity for this date
      const hours = getHoursForDate(preferredDate);
      
      let openingHour = 7;
      let closingHour = 19;
      let isClosed = hours.is_closed;
      let maxSimultaneousBookings = hours.simultaneous_bookings;

      if (!isClosed && hours.opening_time && hours.closing_time) {
        openingHour = parseInt(hours.opening_time.split(':')[0]);
        closingHour = parseInt(hours.closing_time.split(':')[0]);
      }

      if (isClosed) {
        setTimeSlots([]);
        setLoading(false);
        return;
      }

      const { data: appointments, error } = await supabase
        .from('oregon_tires_appointments')
        .select('*')
        .eq('preferred_date', preferredDate)
        .neq('status', 'cancelled');

      if (error) throw error;

      const serviceDuration = serviceDurations[service] || 1.5;
      const serviceDurationMinutes = serviceDuration * 60;
      const slots = generateTimeSlots(openingHour, closingHour);
      const availableSlots: TimeSlot[] = [];

      slots.forEach(startTime => {
        const startMinutes = timeToMinutes(startTime);
        const endMinutes = startMinutes + serviceDurationMinutes;
        const closingTimeMinutes = closingHour * 60;

        let status: 'available' | 'unavailable' = 'available';
        let conflictCount = 0;
        let message = '';

        // Check if time is in the past
        if (isPastTime(preferredDate, startTime)) {
          status = 'unavailable';
          message = 'Time has passed';
        } else if (endMinutes > closingTimeMinutes) {
          status = 'unavailable';
          message = `Service would extend beyond closing time (${closingHour === 12 ? '12:00 PM' : closingHour > 12 ? `${closingHour - 12}:00 PM` : `${closingHour}:00 AM`})`;
        } else {
          let hasConflict = false;

          for (let checkMinutes = startMinutes; checkMinutes < endMinutes; checkMinutes += 30) {
            let slotConflicts = 0;
            
            appointments?.forEach(apt => {
              const aptTimeStr = apt.preferred_time;
              const aptStartMinutes = timeToMinutes(aptTimeStr.substring(0, 5));
              const aptDuration = serviceDurations[apt.service] || 1.5;
              const aptEndMinutes = aptStartMinutes + (aptDuration * 60);

              if (checkMinutes < aptEndMinutes && (checkMinutes + 30) > aptStartMinutes) {
                slotConflicts++;
              }
            });

            if (slotConflicts >= maxSimultaneousBookings) {
              hasConflict = true;
              break;
            }
          }

          if (hasConflict) {
            status = 'unavailable';
            message = 'Time slot conflict - fully booked during service period';
          } else {
            // Both available and limited availability slots are now just "available"
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
  }, [preferredDate, service]);

  return {
    timeSlots,
    loading,
    serviceDuration: serviceDurations[service] || 1.5
  };
};
