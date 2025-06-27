
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Appointment } from '@/types/admin';
import { useEffect } from 'react';

interface AppointmentsTabProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AppointmentsTab = ({ appointments, updateAppointmentStatus }: AppointmentsTabProps) => {
  // Log appointments data for debugging
  useEffect(() => {
    console.log('AppointmentsTab received appointments:', appointments.length);
    console.log('Appointments data:', appointments);
  }, [appointments]);

  const handleStatusChange = async (id: string, newStatus: string) => {
    console.log('Updating appointment status:', { id, status: newStatus });
    await updateAppointmentStatus(id, newStatus);
  };

  const formatStatus = (status: string) => {
    switch (status.toLowerCase()) {
      case 'new':
        return 'New';
      case 'pending':
        return 'Pending';
      case 'confirmed':
        return 'Confirmed';
      case 'completed':
        return 'Completed';
      case 'cancelled':
        return 'Cancelled';
      default:
        return status.charAt(0).toUpperCase() + status.slice(1);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'new':
      case 'pending':
        return 'text-yellow-600 bg-yellow-50';
      case 'confirmed':
        return 'text-blue-600 bg-blue-50';
      case 'completed':
        return 'text-green-600 bg-green-50';
      case 'cancelled':
        return 'text-red-600 bg-red-50';
      default:
        return 'text-gray-600 bg-gray-50';
    }
  };

  return (
    <Card className="border-2" style={{ borderColor: '#007030' }}>
      <CardHeader style={{ backgroundColor: '#007030' }} className="text-white">
        <CardTitle>Service Appointments ({appointments.length})</CardTitle>
      </CardHeader>
      <CardContent className="p-0">
        {appointments.length === 0 ? (
          <p className="text-gray-500 p-6">No appointments found.</p>
        ) : (
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow style={{ backgroundColor: '#FEE11A' }}>
                  <TableHead className="text-black font-semibold">Customer</TableHead>
                  <TableHead className="text-black font-semibold">Service</TableHead>
                  <TableHead className="text-black font-semibold">Date & Time</TableHead>
                  <TableHead className="text-black font-semibold">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {appointments.map((appointment) => (
                  <TableRow key={appointment.id} className="hover:bg-gray-50">
                    <TableCell>
                      <div>
                        <p className="font-medium text-[#007030]">
                          {appointment.first_name} {appointment.last_name}
                        </p>
                        <p className="text-sm text-gray-600">{appointment.phone}</p>
                        <p className="text-sm text-gray-600">{appointment.email}</p>
                        {appointment.message && (
                          <p className="text-sm text-gray-500 mt-1 truncate max-w-[200px]">
                            {appointment.message}
                          </p>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <span className="font-medium">{appointment.service}</span>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        <p className="font-medium">{appointment.preferred_date}</p>
                        <p className="text-gray-600">{appointment.preferred_time}</p>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="flex flex-col gap-2">
                        <span className={`px-2 py-1 rounded text-xs font-medium ${getStatusColor(appointment.status)}`}>
                          {formatStatus(appointment.status)}
                        </span>
                        <Select
                          key={`${appointment.id}-${appointment.status}`}
                          value={appointment.status}
                          onValueChange={(value) => handleStatusChange(appointment.id, value)}
                        >
                          <SelectTrigger className="w-32">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="new">New</SelectItem>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="confirmed">Confirmed</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        )}
      </CardContent>
    </Card>
  );
};
