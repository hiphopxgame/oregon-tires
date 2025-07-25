
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Appointment } from '@/types/admin';
import { useEmployees } from '@/hooks/useEmployees';
import { useLanguage } from '@/hooks/useLanguage';

interface AppointmentsTabProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
}

export const AppointmentsTab = ({ appointments, updateAppointmentStatus, updateAppointmentAssignment }: AppointmentsTabProps) => {
  const { employees } = useEmployees();
  const { t } = useLanguage();
  const getStatusBadge = (status: string) => {
    const normalizedStatus = status.toLowerCase();
    const variants = {
      new: { variant: 'secondary' as const, className: 'bg-blue-500 text-white' },
      confirmed: { variant: 'default' as const, className: 'bg-blue-500 text-white' },
      completed: { variant: 'default' as const, className: 'bg-[#007030] text-white' },
      cancelled: { variant: 'destructive' as const, className: 'bg-red-500 text-white' }
    };

    const variant = variants[normalizedStatus as keyof typeof variants] || variants.new;
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
        <CardTitle>{t.admin.allAppointments}</CardTitle>
        <CardDescription>
          {appointments.length} {t.admin.appointmentsCount} total (sorted by newest first)
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>{t.admin.customer}</TableHead>
              <TableHead>{t.admin.service}</TableHead>
              <TableHead>{t.admin.dateTime}</TableHead>
              <TableHead>{t.admin.contact}</TableHead>
              <TableHead>{t.admin.assignedEmployee}</TableHead>
              <TableHead>{t.admin.status}</TableHead>
              <TableHead>{t.admin.actions}</TableHead>
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
                      {t.admin.created}: {new Date(appointment.created_at).toLocaleDateString()}
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
                    {appointment.status === 'completed' && appointment.actual_duration_minutes && (
                      <div className="text-sm text-green-700 font-medium mt-1">
                        Completed in {appointment.actual_duration_minutes}min
                      </div>
                    )}
                  </div>
                </TableCell>
                <TableCell>
                  <div className="text-sm">
                    <div>{appointment.email}</div>
                    {appointment.phone && <div>{appointment.phone}</div>}
                  </div>
                </TableCell>
                <TableCell>
                  <div className="text-sm">
                    {appointment.assigned_employee_id 
                      ? employees.find(emp => emp.id === appointment.assigned_employee_id)?.name || 'Unknown'
                      : t.admin.unassigned
                    }
                  </div>
                </TableCell>
                <TableCell>
                  {getStatusBadge(appointment.status)}
                </TableCell>
                <TableCell>
                  <div className="flex flex-col gap-2">
                    <Select
                      value={appointment.status}
                      onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
                    >
                      <SelectTrigger className="w-32">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="new">{t.admin.new}</SelectItem>
                        <SelectItem value="confirmed">{t.admin.confirmed}</SelectItem>
                        <SelectItem value="completed">{t.admin.completed}</SelectItem>
                        <SelectItem value="cancelled">{t.admin.cancelled}</SelectItem>
                      </SelectContent>
                    </Select>
                    <Select
                      value={appointment.assigned_employee_id || "unassigned"}
                      onValueChange={(value) => updateAppointmentAssignment(appointment.id, value === "unassigned" ? null : value)}
                    >
                      <SelectTrigger className="w-32">
                        <SelectValue placeholder="Assign" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="unassigned">{t.admin.unassigned}</SelectItem>
                        {employees.map((employee) => (
                          <SelectItem key={employee.id} value={employee.id}>
                            {employee.name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
        
        {appointments.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            {t.admin.noAppointmentsFound}
          </div>
        )}
      </CardContent>
    </Card>
  );
};
