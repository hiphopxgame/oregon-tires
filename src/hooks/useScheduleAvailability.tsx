import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';

interface TimeSlot {
  time: string;
  display: string;
  status: 'available' | 'limited' | 'unavailable';
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

  const serviceDurations: Record<string, number> = {
    'new-or-used-tires': 2,
    'mount-and-balance-tires': 2,
    'tire-repair': 1,
    'oil-change': 1.25,
    'front-or-back-brake-change': 2,
    'full-brake-change': 3.5,
    'tuneup': 5,
    'alignment': 2,
    'mechanical-inspection-and-estimate': 2.5
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
      
      const selectedDate = new Date(preferredDate + 'T00:00:00');
      
      if (selectedDate.getDay() === 0) {
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
      const slots = generateTimeSlots();
      const availableSlots: TimeSlot[] = [];

      slots.forEach(startTime => {
        const startMinutes = timeToMinutes(startTime);
        const endMinutes = startMinutes + serviceDurationMinutes;
        const closingTime = 19 * 60;

        let status: 'available' | 'limited' | 'unavailable' = 'available';
        let conflictCount = 0;
        let message = '';

        if (endMinutes > closingTime) {
          status = 'unavailable';
          message = 'Service would extend beyond closing time (7 PM)';
        } else {
          let hasConflict = false;
          let limitedSlots = 0;

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
  }, [preferredDate, service]);

  return {
    timeSlots,
    loading,
    serviceDuration: serviceDurations[service] || 1.5
  };
};
