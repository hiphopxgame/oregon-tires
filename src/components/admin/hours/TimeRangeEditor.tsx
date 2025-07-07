import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface TimeRangeEditorProps {
  openingTime: string;
  closingTime: string;
  onOpeningTimeChange: (time: string) => void;
  onClosingTimeChange: (time: string) => void;
}

export const TimeRangeEditor = ({
  openingTime,
  closingTime,
  onOpeningTimeChange,
  onClosingTimeChange
}: TimeRangeEditorProps) => {
  return (
    <div className="grid grid-cols-2 gap-4">
      <div className="space-y-2">
        <Label htmlFor="opening-time">Opening Time</Label>
        <Input
          id="opening-time"
          type="time"
          value={openingTime}
          onChange={(e) => onOpeningTimeChange(e.target.value)}
        />
      </div>
      <div className="space-y-2">
        <Label htmlFor="closing-time">Closing Time</Label>
        <Input
          id="closing-time"
          type="time"
          value={closingTime}
          onChange={(e) => onClosingTimeChange(e.target.value)}
        />
      </div>
    </div>
  );
};