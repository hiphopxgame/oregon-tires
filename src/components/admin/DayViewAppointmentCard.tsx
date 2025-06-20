import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { User, Edit2, Save, X } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';

interface EditingAppointment {
  id: string;
  preferred_date: string;
  preferred_time: string;
}

interface DayViewAppointmentCardProps {
  appointment: Appointment;
  extendsAfterHours: boolean;
  formatDuration: (service: string) => string;
  getStatusColor: (status: string) => string;
  capitalizeStatus: (status: string) => string;
  updateAppointmentStatus: (id: string, status: string) => void;
  onAppointmentUpdated?: () => void;
}

export const DayViewAppointmentCard = ({
  appointment,
  extendsAfterHours,
  formatDuration,
  getStatusColor,
  capitalizeStatus,
  updateAppointmentStatus,
  onAppointmentUpdated
}: DayViewAppointmentCardProps) => {
  const [editingAppointment, setEditingAppointment] = useState<EditingAppointment | null>(null);
  const { toast } = useToast();

  const updateAppointmentDateTime = async (appointmentId: string, newDate: string, newTime: string) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({
          preferred_date: newDate,
          preferred_time: newTime
        })
        .eq('id', appointmentId);

      if (error) throw error;

      toast({
        title: "Appointment Updated",
        description: "Date and time have been successfully updated.",
      });

      // Close the editing state
      setEditingAppointment(null);
      
      // Trigger data refresh
      if (onAppointmentUpdated) {
        onAppointmentUpdated();
      }
    } catch (error) {
      console.error('Error updating appointment:', error);
      toast({
        title: "Error",
        description: "Failed to update appointment date/time",
        variant: "destructive",
      });
    }
  };

  const startEditing = (appointment: Appointment) => {
    setEditingAppointment({
      id: appointment.id,
      preferred_date: appointment.preferred_date,
      preferred_time: appointment.preferred_time
    });
  };

  const cancelEditing = () => {
    setEditingAppointment(null);
  };

  const saveEditing = () => {
    if (editingAppointment) {
      updateAppointmentDateTime(
        editingAppointment.id,
        editingAppointment.preferred_date,
        editingAppointment.preferred_time
      );
    }
  };

  const isEditing = editingAppointment?.id === appointment.id;

  return (
    <div className={`border rounded-lg p-3 ${extendsAfterHours ? 'bg-red-50 border-red-200' : 'bg-white'}`}>
      <div className="flex items-start justify-between mb-2">
        <div className="flex items-center gap-2">
          <User className="h-4 w-4 text-gray-500" />
          <div>
            <p className="font-medium text-gray-900">
              {appointment.first_name} {appointment.last_name}
            </p>
            <p className="text-sm text-gray-600">{appointment.phone}</p>
            <p className="text-sm text-gray-600">{appointment.email}</p>
          </div>
        </div>
        <div className="text-right">
          <p className="font-medium text-sm">{appointment.service}</p>
          <p className="text-xs text-gray-500">Duration: {formatDuration(appointment.service)}</p>
          {extendsAfterHours && (
            <p className="text-xs text-red-600 font-medium">⚠️ Extends past 7 PM</p>
          )}
          <div className={`inline-block px-2 py-1 rounded text-xs font-medium border ${getStatusColor(appointment.status)}`}>
            {capitalizeStatus(appointment.status)}
          </div>
        </div>
      </div>

      {/* Date and Time Editing Section */}
      <div className="mb-3 p-3 bg-gray-50 rounded border">
        <div className="flex items-center justify-between mb-2">
          <h4 className="font-medium text-sm text-gray-700">Appointment Date & Time</h4>
          {!isEditing ? (
            <Button
              size="sm"
              variant="outline"
              onClick={() => startEditing(appointment)}
              className="h-7 px-2"
            >
              <Edit2 className="h-3 w-3 mr-1" />
              Edit
            </Button>
          ) : (
            <div className="flex gap-1">
              <Button
                size="sm"
                onClick={saveEditing}
                className="h-7 px-2 bg-green-600 hover:bg-green-700"
              >
                <Save className="h-3 w-3 mr-1" />
                Save
              </Button>
              <Button
                size="sm"
                variant="outline"
                onClick={cancelEditing}
                className="h-7 px-2"
              >
                <X className="h-3 w-3 mr-1" />
                Cancel
              </Button>
            </div>
          )}
        </div>
        
        {isEditing ? (
          <div className="grid grid-cols-2 gap-2">
            <div>
              <label className="text-xs text-gray-600 block mb-1">Date</label>
              <Input
                type="date"
                value={editingAppointment?.preferred_date || ''}
                onChange={(e) => setEditingAppointment(prev => prev ? {...prev, preferred_date: e.target.value} : null)}
                className="h-8 text-xs"
              />
            </div>
            <div>
              <label className="text-xs text-gray-600 block mb-1">Time</label>
              <Input
                type="time"
                value={editingAppointment?.preferred_time || ''}
                onChange={(e) => setEditingAppointment(prev => prev ? {...prev, preferred_time: e.target.value} : null)}
                className="h-8 text-xs"
              />
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-2 gap-2 text-sm">
            <div>
              <span className="text-gray-600">Date:</span>
              <div className="font-medium">{appointment.preferred_date}</div>
            </div>
            <div>
              <span className="text-gray-600">Time:</span>
              <div className="font-medium">{appointment.preferred_time}</div>
            </div>
          </div>
        )}
      </div>

      {appointment.message && (
        <p className="text-sm text-gray-600 mb-2 italic">"{appointment.message}"</p>
      )}

      <div className="flex items-center gap-2">
        <span className="text-xs text-gray-500">Status:</span>
        <Select
          value={capitalizeStatus(appointment.status)}
          onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
        >
          <SelectTrigger className="w-32 h-8 text-xs">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="New">New</SelectItem>
            <SelectItem value="Priority">Priority</SelectItem>
            <SelectItem value="Completed">Completed</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>
  );
};
