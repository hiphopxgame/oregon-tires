import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, User, MapPin, CalendarX } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { useEmployees } from '@/hooks/useEmployees';
import { useLanguage } from '@/hooks/useLanguage';
import { format, addDays, startOfDay, isAfter, isBefore } from 'date-fns';

interface DashboardOverviewProps {
  appointments: Appointment[];
}

export const DashboardOverview: React.FC<DashboardOverviewProps> = ({ appointments }) => {
  const { employees } = useEmployees();
  const { t } = useLanguage();

  // Get the next 14 days starting from today
  const today = startOfDay(new Date());
  const next14Days = Array.from({ length: 14 }, (_, i) => addDays(today, i));

  // Filter appointments for the next 14 days
  const upcomingAppointments = appointments.filter(apt => {
    const aptDate = new Date(apt.preferred_date + 'T00:00:00');
    const endOfFourteenthDay = addDays(today, 14);
    return isAfter(aptDate, addDays(today, -1)) && isBefore(aptDate, endOfFourteenthDay);
  });

  // Group appointments by date
  const appointmentsByDate = next14Days.map(date => {
    const dateStr = format(date, 'yyyy-MM-dd');
    const dayAppointments = upcomingAppointments.filter(apt => apt.preferred_date === dateStr);
    return {
      date,
      dateStr,
      appointments: dayAppointments
    };
  });

  // Calculate statistics
  const totalAppointments = upcomingAppointments.length;
  const pendingAppointments = upcomingAppointments.filter(apt => apt.status === 'new').length;
  const confirmedAppointments = upcomingAppointments.filter(apt => apt.status === 'confirmed').length;
  const completedAppointments = upcomingAppointments.filter(apt => apt.status === 'completed').length;

  // Generate employee schedule for the next 14 days
  const employeeSchedule = employees.filter(emp => emp.is_active).map(employee => {
    const employeeAppointments = upcomingAppointments.filter(apt => apt.assigned_employee_id === employee.id);
    return {
      employee,
      appointments: employeeAppointments,
      totalHours: employeeAppointments.length * 1.5, // Assuming 1.5 hours per appointment
    };
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'new': return 'bg-blue-100 text-blue-800';
      case 'confirmed': return 'bg-green-100 text-green-800';
      case 'in_progress': return 'bg-yellow-100 text-yellow-800';
      case 'completed': return 'bg-gray-100 text-gray-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="space-y-6">
      {/* Week Overview Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">{t.admin.totalAppointments}</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalAppointments}</div>
            <p className="text-xs text-muted-foreground">{t.admin.next14Days}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">{t.admin.pending}</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">{pendingAppointments}</div>
            <p className="text-xs text-muted-foreground">{t.admin.awaitingConfirmation}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">{t.admin.confirmed}</CardTitle>
            <User className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">{confirmedAppointments}</div>
            <p className="text-xs text-muted-foreground">{t.admin.readyToGo}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">{t.admin.completed}</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-gray-600">{completedAppointments}</div>
            <p className="text-xs text-muted-foreground">{t.admin.finished}</p>
          </CardContent>
        </Card>
      </div>

      {/* Weekly Schedule */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Daily Appointments */}
        <Card>
          <CardHeader>
            <CardTitle>Upcoming Appointments</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {appointmentsByDate.map(({ date, appointments: dayAppointments }) => (
                <div key={date.toISOString()} className="border-l-4 border-primary pl-4">
                  <div className="flex items-center justify-between mb-2">
                    <h4 className="font-medium">{format(date, 'EEEE, MMMM d, yyyy')}</h4>
                    {dayAppointments.length === 0 ? (
                      <div className="flex items-center gap-2">
                        <CalendarX className="h-4 w-4 text-orange-600" />
                        <Badge variant="outline" className="border-orange-600 text-orange-600">
                          {t.admin.noAppointments}
                        </Badge>
                      </div>
                    ) : (
                      <Badge variant="outline">{dayAppointments.length} {t.admin.appointmentsCount}</Badge>
                    )}
                  </div>
                  {dayAppointments.length > 0 ? (
                    <div className="space-y-2">
                      {dayAppointments.slice(0, 3).map(apt => (
                        <a 
                          key={apt.id} 
                          href={`#appointment-${apt.id}`}
                          className="text-sm flex items-center justify-between bg-muted/50 p-2 rounded hover:bg-muted/70 transition-colors cursor-pointer"
                        >
                          <div>
                            <span className="font-medium">{apt.preferred_time}</span> - {apt.first_name} {apt.last_name}
                          </div>
                          <Badge className={getStatusColor(apt.status)} variant="secondary">
                            {t.admin.status[apt.status] || apt.status}
                          </Badge>
                        </a>
                      ))}
                      {dayAppointments.length > 3 && (
                        <p className="text-xs text-muted-foreground">+{dayAppointments.length - 3} {t.admin.more}</p>
                      )}
                    </div>
                  ) : (
                    <div className="text-center py-4 text-gray-500">
                      <CalendarX className="h-8 w-8 mx-auto mb-2 text-gray-400" />
                      <p className="text-sm text-muted-foreground">{t.admin.dayAvailableForBooking}</p>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Employee Schedule */}
        <Card>
          <CardHeader>
            <CardTitle>{t.admin.employeeSchedule}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {employeeSchedule.map(({ employee, appointments, totalHours }) => (
                <div key={employee.id} className="border rounded-lg p-4">
                  <div className="flex items-center justify-between mb-3">
                    <div>
                      <h4 className="font-medium">{employee.name}</h4>
                      <p className="text-sm text-muted-foreground">{employee.email}</p>
                    </div>
                    <div className="text-right">
                      <p className="text-sm font-medium">{appointments.length} {t.admin.appointmentsCount}</p>
                      <p className="text-xs text-muted-foreground">~{totalHours} {t.admin.hours}</p>
                    </div>
                  </div>
                  
                  {appointments.length > 0 ? (
                    <div className="space-y-2">
                      {appointments.slice(0, 2).map(apt => (
                        <a 
                          key={apt.id} 
                          href={`#appointment-${apt.id}`}
                          className="text-sm flex items-center gap-2 bg-muted/30 p-2 rounded hover:bg-muted/50 transition-colors cursor-pointer"
                        >
                          <Calendar className="h-3 w-3" />
                          <span>{format(new Date(apt.preferred_date), 'MMM d')} {apt.preferred_time}</span>
                          {apt.service_location === 'mobile' && <MapPin className="h-3 w-3" />}
                        </a>
                      ))}
                      {appointments.length > 2 && (
                        <p className="text-xs text-muted-foreground pl-5">+{appointments.length - 2} {t.admin.moreAppointments}</p>
                      )}
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">{t.admin.noAppointmentsNext14Days}</p>
                  )}
                </div>
              ))}
              
              {employeeSchedule.length === 0 && (
                <p className="text-muted-foreground text-center py-4">{t.admin.noActiveEmployees}</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};