import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Clock, Save, RotateCcw } from 'lucide-react';
import { CustomHours, useCustomHours } from '@/hooks/useCustomHours';

interface HoursEditorProps {
  selectedDate: Date;
}

export const HoursEditor = ({ selectedDate }: HoursEditorProps) => {
  const { getHoursForDate, updateCustomHours, deleteCustomHours } = useCustomHours();
  const [saving, setSaving] = useState(false);
  
  const dateStr = selectedDate.toISOString().split('T')[0];
  const currentHours = getHoursForDate(dateStr);
  
  const [isClosed, setIsClosed] = useState(currentHours.is_closed);
  const [openingTime, setOpeningTime] = useState(currentHours.opening_time || '07:00');
  const [closingTime, setClosingTime] = useState(currentHours.closing_time || '19:00');

  const formatDateDisplay = (date: Date) => {
    return date.toLocaleDateString('en-US', { 
      weekday: 'long', 
      month: 'long', 
      day: 'numeric',
      year: 'numeric'
    });
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      await updateCustomHours(dateStr, {
        is_closed: isClosed,
        opening_time: isClosed ? null : openingTime,
        closing_time: isClosed ? null : closingTime,
      });
    } catch (error) {
      // Error handled in hook
    } finally {
      setSaving(false);
    }
  };

  const handleReset = async () => {
    try {
      setSaving(true);
      await deleteCustomHours(dateStr);
      
      // Reset to default values
      const dayOfWeek = selectedDate.getDay();
      if (dayOfWeek === 0) { // Sunday
        setIsClosed(true);
        setOpeningTime('07:00');
        setClosingTime('19:00');
      } else {
        setIsClosed(false);
        setOpeningTime('07:00');
        setClosingTime('19:00');
      }
    } catch (error) {
      // Error handled in hook
    } finally {
      setSaving(false);
    }
  };

  const hasChanges = () => {
    return (
      isClosed !== currentHours.is_closed ||
      (!isClosed && openingTime !== (currentHours.opening_time || '07:00')) ||
      (!isClosed && closingTime !== (currentHours.closing_time || '19:00'))
    );
  };

  const isCustomHours = currentHours.id !== '';

  return (
    <Card className="border-2 border-green-700">
      <CardHeader className="bg-green-700 text-white">
        <CardTitle className="flex items-center gap-2">
          <Clock className="h-5 w-5" />
          Store Hours - {formatDateDisplay(selectedDate)}
        </CardTitle>
      </CardHeader>
      <CardContent className="p-4 space-y-4">
        <div className="flex items-center justify-between">
          <Label htmlFor="closed-switch" className="font-medium">
            Store is closed this day
          </Label>
          <Switch
            id="closed-switch"
            checked={isClosed}
            onCheckedChange={setIsClosed}
          />
        </div>

        {!isClosed && (
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="opening-time">Opening Time</Label>
              <Input
                id="opening-time"
                type="time"
                value={openingTime}
                onChange={(e) => setOpeningTime(e.target.value)}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="closing-time">Closing Time</Label>
              <Input
                id="closing-time"
                type="time"
                value={closingTime}
                onChange={(e) => setClosingTime(e.target.value)}
              />
            </div>
          </div>
        )}

        <div className="flex gap-2 pt-2">
          <Button
            onClick={handleSave}
            disabled={!hasChanges() || saving}
            className="flex items-center gap-2"
          >
            <Save className="h-4 w-4" />
            {saving ? 'Saving...' : 'Save Hours'}
          </Button>
          
          {isCustomHours && (
            <Button
              variant="outline"
              onClick={handleReset}
              disabled={saving}
              className="flex items-center gap-2"
            >
              <RotateCcw className="h-4 w-4" />
              Reset to Default
            </Button>
          )}
        </div>

        {isCustomHours && (
          <div className="text-sm text-blue-600 bg-blue-50 p-2 rounded">
            ℹ️ This date has custom hours that override the default schedule.
          </div>
        )}

        <div className="text-sm text-gray-600 bg-gray-50 p-2 rounded">
          <strong>Default Schedule:</strong><br />
          Sunday: Closed<br />
          Monday - Saturday: 7:00 AM - 7:00 PM
        </div>
      </CardContent>
    </Card>
  );
};