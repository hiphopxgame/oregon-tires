
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Appointment } from '@/types/admin';

interface AppointmentsTabProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AppointmentsTab = ({ appointments, updateAppointmentStatus }: AppointmentsTabProps) => {
  const getStatusBadge = (status: string) => {
    const normalizedStatus = status.toLowerCase();
    const variants = {
      confirmed: { variant: 'default' as const, className: 'bg-blue-500 text-white' },
      completed: { variant: 'default' as const, className: 'bg-[#007030] text-white' },
      cancelled: { variant: 'destructive' as const, className: 'bg-red-500 text-white' }
    };

    const variant = variants[normalizedStatus as keyof typeof variants] || variants.confirmed;
    return (
      <Badge variant={variant.variant} className={variant.className}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  // Sort appointments by creation date, newest first
  const sortedAppointments = [...appointments].sort((a, b) => 
    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  );

  return (
    <Card>
      <CardHeader>
        <CardTitle>All Appointments</CardTitle>
        <CardDescription>
          {appointments.length} appointments total (sorted by newest first)
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Customer</TableHead>
              <TableHead>Service</TableHead>
              <TableHead>Date & Time</TableHead>
              <TableHead>Contact</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {sortedAppointments.map((appointment) => (
              <TableRow key={appointment.id}>
                <TableCell>
                  <div>
                    <div className="font-medium">
                      {appointment.first_name} {appointment.last_name}
                    </div>
                    <div className="text-sm text-gray-500">
                      Created: {new Date(appointment.created_at).toLocaleDateString()}
                    </div>
                  </div>
                </TableCell>
                <TableCell>{appointment.service}</TableCell>
                <TableCell>
                  <div>
                    <div className="font-medium">
                      {new Date(appointment.preferred_date + 'T00:00:00').toLocaleDateString()}
                    </div>
                    <div className="text-sm text-gray-500">
                      {appointment.preferred_time}
                    </div>
                  </div>
                </TableCell>
                <TableCell>
                  <div className="text-sm">
                    <div>{appointment.email}</div>
                    {appointment.phone && <div>{appointment.phone}</div>}
                  </div>
                </TableCell>
                <TableCell>
                  {getStatusBadge(appointment.status)}
                </TableCell>
                <TableCell>
                  <Select
                    value={appointment.status}
                    onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
                  >
                    <SelectTrigger className="w-32">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="confirmed">Confirmed</SelectItem>
                      <SelectItem value="completed">Completed</SelectItem>
                      <SelectItem value="cancelled">Cancelled</SelectItem>
                    </SelectContent>
                  </Select>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
        
        {appointments.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            No appointments found
          </div>
        )}
      </CardContent>
    </Card>
  );
};
