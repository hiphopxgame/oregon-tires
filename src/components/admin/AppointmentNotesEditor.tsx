import { useState } from 'react';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { useLanguage } from '@/hooks/useLanguage';
import { Appointment } from '@/types/admin';

interface AppointmentNotesEditorProps {
  appointment: Appointment;
  onNotesUpdate?: () => void;
}

export const AppointmentNotesEditor = ({ appointment, onNotesUpdate }: AppointmentNotesEditorProps) => {
  const [notes, setNotes] = useState(appointment.admin_notes || '');
  const [isLoading, setIsLoading] = useState(false);
  const { toast } = useToast();
  const { t } = useLanguage();

  const handleSaveNotes = async () => {
    setIsLoading(true);
    try {
      const { error } = await supabase
        .from('oretir_appointments')
        .update({ admin_notes: notes || null })
        .eq('id', appointment.id);

      if (error) throw error;

      toast({
        title: t.admin.success,
        description: 'Admin notes updated successfully',
      });

      onNotesUpdate?.();
    } catch (error) {
      console.error('Error updating notes:', error);
      toast({
        title: t.admin.error,
        description: 'Failed to update admin notes',
        variant: 'destructive',
      });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-sm font-medium">Admin Notes</CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        <Textarea
          value={notes}
          onChange={(e) => setNotes(e.target.value)}
          placeholder="Add internal notes for this appointment..."
          rows={3}
          className="resize-none"
        />
        <div className="flex justify-end">
          <Button
            onClick={handleSaveNotes}
            disabled={isLoading || notes === (appointment.admin_notes || '')}
            size="sm"
          >
            {isLoading ? 'Saving...' : 'Save Notes'}
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};