import { Button } from '@/components/ui/button';
import { Save, RotateCcw } from 'lucide-react';

interface HoursActionsProps {
  hasChanges: boolean;
  saving: boolean;
  isCustomHours: boolean;
  onSave: () => void;
  onReset: () => void;
}

export const HoursActions = ({
  hasChanges,
  saving,
  isCustomHours,
  onSave,
  onReset
}: HoursActionsProps) => {
  return (
    <div className="flex gap-2 pt-2">
      <Button
        onClick={onSave}
        disabled={!hasChanges || saving}
        className="flex items-center gap-2"
      >
        <Save className="h-4 w-4" />
        {saving ? 'Saving...' : 'Save Hours'}
      </Button>
      
      {isCustomHours && (
        <Button
          variant="outline"
          onClick={onReset}
          disabled={saving}
          className="flex items-center gap-2"
        >
          <RotateCcw className="h-4 w-4" />
          Reset to Default
        </Button>
      )}
    </div>
  );
};