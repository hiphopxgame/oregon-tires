import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ChevronDown, ChevronUp, Calendar, Clock, User, Phone, Mail } from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { Appointment } from '@/types/admin';

interface EmployeeAppointmentsProps {
  employeeId: string;
  employeeName: string;
}

export const EmployeeAppointments = ({ employeeId, employeeName }: EmployeeAppointmentsProps) => {
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [loading, setLoading] = useState(false);
  const [isExpanded, setIsExpanded] = useState(false);

  const fetchAppointments = async () => {
    if (!isExpanded) return;
    
    setLoading(true);
    try {
      const { data, error } = await supabase
        .from('oregon_tires_appointments')
        .select('*')
        .eq('assigned_employee_id', employeeId)
        .order('preferred_date', { ascending: false });

      if (error) throw error;
      setAppointments(data || []);
    } catch (error) {
      console.error('Error fetching employee appointments:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAppointments();
  }, [isExpanded, employeeId]);

  const getStatusBadgeVariant = (status: string) => {
    switch (status) {
      case 'confirmed':
        return 'default';
      case 'pending':
        return 'secondary';
      case 'completed':
        return 'outline';
      case 'cancelled':
        return 'destructive';
      default:
        return 'secondary';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  return (
    <div className="mt-3 border-t pt-3">
      <Button
        variant="ghost"
        size="sm"
        onClick={() => setIsExpanded(!isExpanded)}
        className="w-full justify-between text-sm"
      >
        <span className="flex items-center gap-2">
          <Calendar className="h-4 w-4" />
          Assigned Appointments
        </span>
        {isExpanded ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
      </Button>

      {isExpanded && (
        <div className="mt-3 space-y-2">
          {loading ? (
            <div className="text-sm text-gray-500 text-center py-4">Loading appointments...</div>
          ) : appointments.length === 0 ? (
            <div className="text-sm text-gray-500 text-center py-4">
              No appointments assigned to {employeeName}
            </div>
          ) : (
            appointments.map((appointment) => (
              <div key={appointment.id} className="border rounded-lg p-3 bg-gray-50">
                <div className="flex items-start justify-between mb-2">
                  <div className="flex items-center gap-2">
                    <User className="h-4 w-4 text-gray-500" />
                    <span className="font-medium text-sm">
                      {appointment.first_name} {appointment.last_name}
                    </span>
                  </div>
                  <Badge variant={getStatusBadgeVariant(appointment.status)}>
                    {appointment.status}
                  </Badge>
                </div>
                
                <div className="space-y-1 text-xs text-gray-600">
                  <div className="flex items-center gap-2">
                    <Calendar className="h-3 w-3" />
                    <span>{formatDate(appointment.preferred_date)}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Clock className="h-3 w-3" />
                    <span>{appointment.preferred_time}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="font-medium">Service:</span>
                    <span>{appointment.service}</span>
                  </div>
                  {appointment.phone && (
                    <div className="flex items-center gap-2">
                      <Phone className="h-3 w-3" />
                      <span>{appointment.phone}</span>
                    </div>
                  )}
                  <div className="flex items-center gap-2">
                    <Mail className="h-3 w-3" />
                    <span>{appointment.email}</span>
                  </div>
                  {appointment.message && (
                    <div className="mt-2 p-2 bg-white rounded text-xs">
                      <strong>Message:</strong> {appointment.message}
                    </div>
                  )}
                </div>
              </div>
            ))
          )}
        </div>
      )}
    </div>
  );
};