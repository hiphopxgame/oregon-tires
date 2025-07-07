import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Clock } from 'lucide-react';
import { useHoursEditor } from './hours/useHoursEditor';
import { ClosedToggle } from './hours/ClosedToggle';
import { TimeRangeEditor } from './hours/TimeRangeEditor';
import { SimultaneousBookingsEditor } from './hours/SimultaneousBookingsEditor';
import { HoursActions } from './hours/HoursActions';
import { HoursInfo } from './hours/HoursInfo';

interface HoursEditorProps {
  selectedDate: Date;
}

export const HoursEditor = ({ selectedDate }: HoursEditorProps) => {
  const {
    isClosed,
    openingTime,
    closingTime,
    simultaneousBookings,
    saving,
    hasChanges,
    isCustomHours,
    setIsClosed,
    setOpeningTime,
    setClosingTime,
    setSimultaneousBookings,
    handleSave,
    handleReset
  } = useHoursEditor(selectedDate);

  const formatDateDisplay = (date: Date) => {
    return date.toLocaleDateString('en-US', { 
      weekday: 'long', 
      month: 'long', 
      day: 'numeric',
      year: 'numeric'
    });
  };

  return (
    <Card className="border-2 border-green-700">
      <CardHeader className="bg-green-700 text-white">
        <CardTitle className="flex items-center gap-2">
          <Clock className="h-5 w-5" />
          Store Hours - {formatDateDisplay(selectedDate)}
        </CardTitle>
      </CardHeader>
      <CardContent className="p-4 space-y-4">
        <ClosedToggle 
          isClosed={isClosed} 
          onToggle={setIsClosed} 
        />

        {!isClosed && (
          <div className="space-y-4">
            <TimeRangeEditor
              openingTime={openingTime}
              closingTime={closingTime}
              onOpeningTimeChange={setOpeningTime}
              onClosingTimeChange={setClosingTime}
            />
            
            <SimultaneousBookingsEditor
              simultaneousBookings={simultaneousBookings}
              onSimultaneousBookingsChange={setSimultaneousBookings}
            />
          </div>
        )}

        <HoursActions
          hasChanges={hasChanges}
          saving={saving}
          isCustomHours={isCustomHours}
          onSave={handleSave}
          onReset={handleReset}
        />

        <HoursInfo isCustomHours={isCustomHours} />
      </CardContent>
    </Card>
  );
};