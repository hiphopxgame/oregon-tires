
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, Clock } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { DailySummary } from './DailySummary';
import { TimeSlot } from './TimeSlot';
import { EmployeeScheduleAlert } from './EmployeeScheduleAlert';
import { EmployeeAppointments } from './EmployeeAppointments';
import { useEmployees } from '@/hooks/useEmployees';

interface DaySchedulePanelProps {
  selectedDate: Date;
  appointments: Appointment[];
  navigateDate: (direction: 'prev' | 'next') => void;
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
}

interface Employee {
  id: string;
  name: string;
  is_active: boolean;
}

export const DaySchedulePanel = ({
  selectedDate,
  appointments,
  navigateDate,
  updateAppointmentStatus,
  updateAppointmentAssignment
}: DaySchedulePanelProps) => {
  const { employees } = useEmployees();
  // Generate time slots from 7 AM to 7 PM
  const generateTimeSlots = () => {
    const slots = [];
    for (let hour = 7; hour <= 19; hour++) {
      slots.push(`${hour.toString().padStart(2, '0')}:00`);
    }
    return slots;
  };

  const timeSlots = generateTimeSlots();

  // Get appointments for specific time slot
  const getAppointmentsForTimeSlot = (timeSlot: string) => {
    const slotAppointments = appointments.filter(apt => {
      const appointmentTime = apt.preferred_time;
      const appointmentHour = appointmentTime.split(':')[0];
      const slotHour = timeSlot.split(':')[0];
      return appointmentHour === slotHour;
    });
    return slotAppointments;
  };

  return (
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
        <DailySummary appointments={appointments} />

        {/* Employee Schedule Alert */}
        <div className="mb-4">
          <EmployeeScheduleAlert 
            selectedDate={selectedDate}
            appointments={appointments}
            employees={employees}
          />
        </div>

        {/* Employee Assignment Panel */}
        <div className="mb-4">
          <EmployeeAppointments
            appointments={appointments}
            employees={employees}
            selectedDate={selectedDate}
            updateAppointmentAssignment={updateAppointmentAssignment}
          />
        </div>

        {/* Debug info - remove this later */}
        <div className="mb-4 p-2 bg-blue-50 rounded text-xs">
          <p>Selected Date: {selectedDate.toISOString().split('T')[0]}</p>
          <p>Appointments for this date: {appointments.length}</p>
        </div>

        {/* Time Slots */}
        <div className="space-y-2">
          {timeSlots.map((timeSlot) => {
            const slotAppointments = getAppointmentsForTimeSlot(timeSlot);
            
            return (
              <TimeSlot
                key={timeSlot}
                timeSlot={timeSlot}
                appointments={slotAppointments}
                updateAppointmentStatus={updateAppointmentStatus}
                updateAppointmentAssignment={updateAppointmentAssignment}
              />
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
};
