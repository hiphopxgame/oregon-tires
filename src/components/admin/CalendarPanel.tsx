
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
  );
};
