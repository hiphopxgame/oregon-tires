import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Users } from 'lucide-react';

interface SimultaneousBookingsEditorProps {
  simultaneousBookings: number;
  onSimultaneousBookingsChange: (count: number) => void;
}

export const SimultaneousBookingsEditor = ({
  simultaneousBookings,
  onSimultaneousBookingsChange
}: SimultaneousBookingsEditorProps) => {
  return (
    <div className="space-y-2">
      <div className="flex items-center gap-2">
        <Users className="h-4 w-4 text-green-700" />
        <Label htmlFor="simultaneous-bookings">Simultaneous Bookings</Label>
      </div>
      <Input
        id="simultaneous-bookings"
        type="number"
        min="1"
        max="10"
        value={simultaneousBookings}
        onChange={(e) => onSimultaneousBookingsChange(parseInt(e.target.value) || 1)}
        className="w-32"
      />
      <p className="text-xs text-gray-600">
        Number of customers that can book the same time slot
      </p>
    </div>
  );
};