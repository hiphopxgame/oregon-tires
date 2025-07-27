
import { useState } from 'react';
import { format } from 'date-fns';
import { CalendarIcon } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Appointment } from '@/types/admin';
import { useEmployees } from '@/hooks/useEmployees';
import { useLanguage } from '@/hooks/useLanguage';
import { cn } from '@/lib/utils';

interface AppointmentsTabProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
}

export const AppointmentsTab = ({ appointments, updateAppointmentStatus, updateAppointmentAssignment }: AppointmentsTabProps) => {
  const { employees } = useEmployees();
  const { t } = useLanguage();
  
  // Pagination and filtering state
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [selectedMonth, setSelectedMonth] = useState<Date | undefined>();
  
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

  // Filter by month if selected
  const filteredAppointments = selectedMonth 
    ? appointments.filter(appointment => {
        const appointmentDate = new Date(appointment.preferred_date + 'T00:00:00');
        return appointmentDate.getMonth() === selectedMonth.getMonth() && 
               appointmentDate.getFullYear() === selectedMonth.getFullYear();
      })
    : appointments;

  // Sort appointments: unassigned first, then by creation date within each group
  const sortedAppointments = [...filteredAppointments].sort((a, b) => {
    // First priority: unassigned appointments come first
    const aUnassigned = !a.assigned_employee_id;
    const bUnassigned = !b.assigned_employee_id;
    
    if (aUnassigned && !bUnassigned) return -1;
    if (!aUnassigned && bUnassigned) return 1;
    
    // Second priority: within each group, sort by creation date (newest first)
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });

  // Pagination calculations
  const showAllItems = itemsPerPage === -1;
  const totalPages = showAllItems ? 1 : Math.ceil(sortedAppointments.length / itemsPerPage);
  const startIndex = showAllItems ? 0 : (currentPage - 1) * itemsPerPage;
  const endIndex = showAllItems ? sortedAppointments.length : startIndex + itemsPerPage;
  const currentAppointments = sortedAppointments.slice(startIndex, endIndex);

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
  };

  const handleItemsPerPageChange = (value: string) => {
    const newItemsPerPage = value === "all" ? -1 : parseInt(value);
    setItemsPerPage(newItemsPerPage);
    setCurrentPage(1); // Reset to first page
  };

  const handleMonthChange = (month: Date | undefined) => {
    setSelectedMonth(month);
    setCurrentPage(1); // Reset to first page when filtering
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>{t.admin.allAppointments}</CardTitle>
        <CardDescription>
          {filteredAppointments.length} {selectedMonth ? 'filtered' : 'total'} appointments
          {selectedMonth && ` for ${format(selectedMonth, 'MMMM yyyy')}`}
          {showAllItems ? '' : ` - Showing ${currentAppointments.length} on page ${currentPage} of ${totalPages}`}
        </CardDescription>
      </CardHeader>
      <CardContent>
        {/* Filter Controls */}
        <div className="flex flex-wrap gap-4 mb-6">
          {/* Items per page selector */}
          <div className="flex items-center gap-2">
            <label className="text-sm font-medium">Show:</label>
            <Select value={itemsPerPage === -1 ? "all" : itemsPerPage.toString()} onValueChange={handleItemsPerPageChange}>
              <SelectTrigger className="w-24">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="10">10</SelectItem>
                <SelectItem value="25">25</SelectItem>
                <SelectItem value="50">50</SelectItem>
                <SelectItem value="100">100</SelectItem>
                <SelectItem value="all">All</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* Month filter */}
          <div className="flex items-center gap-2">
            <label className="text-sm font-medium">Month:</label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  className={cn(
                    "w-[200px] justify-start text-left font-normal",
                    !selectedMonth && "text-muted-foreground"
                  )}
                >
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {selectedMonth ? format(selectedMonth, "MMMM yyyy") : "All months"}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  mode="single"
                  selected={selectedMonth}
                  onSelect={handleMonthChange}
                  className={cn("p-3 pointer-events-auto")}
                  initialFocus
                />
              </PopoverContent>
            </Popover>
            
            {/* Clear month filter button */}
            {selectedMonth && (
              <Button variant="outline" size="sm" onClick={() => handleMonthChange(undefined)}>
                Clear
              </Button>
            )}
          </div>
        </div>
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
            {currentAppointments.map((appointment) => (
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

        {/* Pagination */}
        {totalPages > 1 && (
          <div className="mt-6">
            <Pagination>
              <PaginationContent>
                <PaginationItem>
                  <PaginationPrevious 
                    onClick={() => handlePageChange(Math.max(1, currentPage - 1))}
                    className={currentPage === 1 ? "pointer-events-none opacity-50" : "cursor-pointer"}
                  />
                </PaginationItem>
                
                {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                  <PaginationItem key={page}>
                    <PaginationLink
                      onClick={() => handlePageChange(page)}
                      isActive={currentPage === page}
                      className="cursor-pointer"
                    >
                      {page}
                    </PaginationLink>
                  </PaginationItem>
                ))}
                
                <PaginationItem>
                  <PaginationNext 
                    onClick={() => handlePageChange(Math.min(totalPages, currentPage + 1))}
                    className={currentPage === totalPages ? "pointer-events-none opacity-50" : "cursor-pointer"}
                  />
                </PaginationItem>
              </PaginationContent>
            </Pagination>
          </div>
        )}
      </CardContent>
    </Card>
  );
};
