
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar } from '@/components/ui/calendar';
import { Calendar as CalendarIcon } from 'lucide-react';

interface CalendarPanelProps {
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  appointmentDates: Date[];
}

export const CalendarPanel = ({
  selectedDate,
  setSelectedDate,
  appointmentDates
}: CalendarPanelProps) => {
  return (
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
          modifiersClassNames={{
            hasAppointment: "calendar-day-has-appointment"
          }}
        />
        
        {/* Legend */}
        <div className="mt-4 p-3 bg-muted rounded">
          <h4 className="font-medium text-sm mb-2 text-muted-foreground">Legend</h4>
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            <div className="w-3 h-3 bg-appointment-indicator rounded-full border border-background"></div>
            <span>Has Appointments</span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};
