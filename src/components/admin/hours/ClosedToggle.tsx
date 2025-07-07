import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

interface ClosedToggleProps {
  isClosed: boolean;
  onToggle: (closed: boolean) => void;
}

export const ClosedToggle = ({ isClosed, onToggle }: ClosedToggleProps) => {
  return (
    <div className="flex items-center justify-between">
      <Label htmlFor="closed-switch" className="font-medium">
        Store is closed this day
      </Label>
      <Switch
        id="closed-switch"
        checked={isClosed}
        onCheckedChange={onToggle}
      />
    </div>
  );
};