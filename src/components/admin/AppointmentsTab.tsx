
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Appointment } from '@/types/admin';

interface AppointmentsTabProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AppointmentsTab = ({ appointments, updateAppointmentStatus }: AppointmentsTabProps) => {
  const capitalizeStatus = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
  };

  return (
    <Card className="border-2" style={{ borderColor: '#007030' }}>
      <CardHeader style={{ backgroundColor: '#007030' }} className="text-white">
        <CardTitle>Service Appointments</CardTitle>
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
                      <Select
                        value={capitalizeStatus(appointment.status)}
                        onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
                      >
                        <SelectTrigger className="w-32">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="New">New</SelectItem>
                          <SelectItem value="Priority">Priority</SelectItem>
                          <SelectItem value="Completed">Completed</SelectItem>
                        </SelectContent>
                      </Select>
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
