import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertTriangle, Clock } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { DayViewTimeSlot } from './DayViewTimeSlot';
import { useLanguage } from '@/hooks/useLanguage';

interface DayViewProps {
  appointments: Appointment[];
  selectedDate: Date;
  updateAppointmentStatus: (id: string, status: string) => void;
  onDataRefresh?: () => void;
}

interface TimeSlot {
  time: string;
  appointments: Appointment[];
  hasOverlap: boolean;
  conflictReason?: string;
}

interface ConflictDetail {
  timeSlot: string;
  appointments: Appointment[];
  conflictType: string;
}

export const DayView = ({ appointments, selectedDate, updateAppointmentStatus, onDataRefresh }: DayViewProps) => {
  const { t } = useLanguage();
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [overlapWarnings, setOverlapWarnings] = useState<string[]>([]);
  const [detailedConflicts, setDetailedConflicts] = useState<ConflictDetail[]>([]);

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

  // Check for appointment overlaps and conflicts
  const checkOverlaps = (dayAppointments: Appointment[]) => {
    const warnings: string[] = [];
    const conflicts: ConflictDetail[] = [];
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
      const currentInterval = appointmentIntervals[i];
      const overlappingAppointments = [currentInterval.appointment];
      
      for (let j = 0; j < appointmentIntervals.length; j++) {
        if (i === j) continue;
        
        const otherInterval = appointmentIntervals[j];
        
        // Check if intervals overlap
        if (currentInterval.startMinutes < otherInterval.endMinutes && 
            currentInterval.endMinutes > otherInterval.startMinutes) {
          overlappingAppointments.push(otherInterval.appointment);
        }
      }
      
      // If more than 2 appointments overlap, it's a conflict
      if (overlappingAppointments.length > 2) {
        const timeSlot = currentInterval.appointment.preferred_time.substring(0, 5);
        
        // Check if we've already recorded this conflict
        const existingConflict = conflicts.find(c => c.timeSlot === timeSlot);
        if (!existingConflict) {
          conflicts.push({
            timeSlot,
            appointments: overlappingAppointments,
            conflictType: `${overlappingAppointments.length} appointments overlapping (max 2 allowed)`
          });
        }
        
        warnings.push(`${currentInterval.appointment.first_name} ${currentInterval.appointment.last_name}'s appointment at ${currentInterval.appointment.preferred_time} conflicts with ${overlappingAppointments.length - 1} other appointments (maximum 2 simultaneous allowed)`);
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

    console.log('Detailed Conflicts Found:', conflicts);
    setTimeSlots(slots);
    setOverlapWarnings(warnings);
    setDetailedConflicts(conflicts);
  };

  const handleAppointmentUpdated = () => {
    if (onDataRefresh) {
      onDataRefresh();
    }
  };

  useEffect(() => {
    const dateStr = selectedDate.toISOString().split('T')[0];
    const dayAppointments = appointments.filter(apt => apt.preferred_date === dateStr);
    checkOverlaps(dayAppointments);
  }, [appointments, selectedDate]);

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

      {/* Detailed Conflict Information */}
      {detailedConflicts.length > 0 && (
        <Alert variant="destructive" className="border-red-200 bg-red-50">
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription>
            <div className="font-semibold mb-3">{t.admin.detailedConflicts}:</div>
            {detailedConflicts.map((conflict, index) => (
              <div key={index} className="mb-4 p-3 bg-white rounded border border-red-200">
                <div className="font-medium text-red-800 mb-2">
                  Time Slot {conflict.timeSlot}: {conflict.conflictType}
                </div>
                <div className="space-y-2">
                  {conflict.appointments.map((apt, aptIndex) => (
                    <div key={apt.id} className="pl-4 border-l-2 border-red-300">
                      <div className="font-medium">{aptIndex + 1}. {apt.first_name} {apt.last_name}</div>
                      <div className="text-sm text-gray-600">
                        Service: {apt.service} | Phone: {apt.phone} | Status: {apt.status}
                      </div>
                      <div className="text-sm text-gray-600">
                        Scheduled: {apt.preferred_date} at {apt.preferred_time}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </AlertDescription>
        </Alert>
      )}

      {/* Original Overlap Warnings */}
      {overlapWarnings.length > 0 && (
        <Alert variant="destructive" className="border-red-200 bg-red-50">
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription>
            <div className="font-semibold mb-2">{t.admin.schedulingConflicts} Detected:</div>
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
          <h3 className="font-semibold mb-2">{t.admin.servicesDurations}:</h3>
          <div className="grid grid-cols-3 gap-4 text-sm">
            <div><strong>{t.admin.tireServices}:</strong> 1.5 {t.admin.hours}</div>
            <div><strong>{t.admin.brakeServices}:</strong> 2.5 {t.admin.hours}</div>
            <div><strong>{t.admin.otherServices}:</strong> 3.5 {t.admin.hours}</div>
          </div>
          <p className="text-xs text-gray-600 mt-2">{t.admin.businessHoursNote}</p>
        </CardContent>
      </Card>

      {/* Time Slots Grid */}
      <div className="grid gap-4">
        {timeSlots.map((slot) => (
          <DayViewTimeSlot
            key={slot.time}
            slot={slot}
            getServiceDuration={getServiceDuration}
            checkBusinessHours={checkBusinessHours}
            formatDuration={formatDuration}
            getStatusColor={getStatusColor}
            capitalizeStatus={capitalizeStatus}
            updateAppointmentStatus={updateAppointmentStatus}
            onAppointmentUpdated={handleAppointmentUpdated}
          />
        ))}
      </div>

      {/* Summary */}
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle>{t.admin.dailySummary}</CardTitle>
        </CardHeader>
        <CardContent className="p-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
              <div className="text-2xl font-bold text-green-600">
                {timeSlots.reduce((sum, slot) => sum + slot.appointments.length, 0)}
              </div>
              <div className="text-sm text-gray-600">{t.admin.totalAppointments}</div>
            </div>
            <div>
              <div className="text-2xl font-bold text-yellow-600">
                {timeSlots.filter(slot => slot.appointments.length === 2).length}
              </div>
              <div className="text-sm text-gray-600">{t.admin.fullTimeSlots}</div>
            </div>
            <div>
              <div className="text-2xl font-bold text-red-600">
                {timeSlots.filter(slot => slot.hasOverlap).length}
              </div>
              <div className="text-sm text-gray-600">{t.admin.overbookedSlots}</div>
            </div>
            <div>
              <div className="text-2xl font-bold text-gray-600">
                {timeSlots.filter(slot => slot.appointments.length === 0).length}
              </div>
              <div className="text-sm text-gray-600">{t.admin.availableSlots}</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
