
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar as CalendarIcon } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { CalendarPanel } from './CalendarPanel';
import { DaySchedulePanel } from './DaySchedulePanel';
import { HoursEditor } from './HoursEditor';

interface ExpandedCalendarViewProps {
  appointments: Appointment[];
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  appointmentDates: Date[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
  onDataRefresh?: () => void;
}

export const ExpandedCalendarView = ({
  appointments,
  selectedDate,
  setSelectedDate,
  appointmentDates,
  updateAppointmentStatus,
  updateAppointmentAssignment,
  onDataRefresh
}: ExpandedCalendarViewProps) => {
  // Get appointments for selected date
  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    const filteredAppointments = appointments.filter(apt => {
      return apt.preferred_date === dateStr;
    });
    return filteredAppointments;
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);

  // Navigate dates
  const navigateDate = (direction: 'prev' | 'next') => {
    const newDate = new Date(selectedDate);
    newDate.setDate(selectedDate.getDate() + (direction === 'next' ? 1 : -1));
    setSelectedDate(newDate);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle className="flex items-center gap-2">
            <CalendarIcon className="h-5 w-5" />
            Calendar Management
          </CardTitle>
        </CardHeader>
      </Card>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Calendar Panel */}
        <div className="lg:col-span-1">
          <CalendarPanel
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
            appointmentDates={appointmentDates}
          />
        </div>

        {/* Day Schedule Panel */}
        <div className="lg:col-span-2 space-y-6">
          <DaySchedulePanel
            selectedDate={selectedDate}
            appointments={selectedDateAppointments}
            navigateDate={navigateDate}
            updateAppointmentStatus={updateAppointmentStatus}
            updateAppointmentAssignment={updateAppointmentAssignment}
          />
          
          {/* Hours Editor */}
          <HoursEditor selectedDate={selectedDate} />
        </div>
      </div>
    </div>
  );
};
