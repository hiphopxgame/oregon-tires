import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, User, MapPin } from 'lucide-react';
import { Appointment } from '@/types/admin';
import { useEmployees } from '@/hooks/useEmployees';

interface DashboardOverviewProps {
  appointments: Appointment[];
}

export const DashboardOverview: React.FC<DashboardOverviewProps> = ({ appointments }) => {
  const { employees } = useEmployees();

  // Get current week's dates
  const today = new Date();
  const weekStart = new Date(today);
  weekStart.setDate(today.getDate() - today.getDay()); // Start of week (Sunday)
  const weekEnd = new Date(weekStart);
  weekEnd.setDate(weekStart.getDate() + 6); // End of week (Saturday)

  // Filter appointments for current week
  const weekAppointments = appointments.filter(apt => {
    const aptDate = new Date(apt.preferred_date);
    return aptDate >= weekStart && aptDate <= weekEnd;
  });

  // Group appointments by day
  const appointmentsByDay = weekAppointments.reduce((acc, apt) => {
    const day = new Date(apt.preferred_date).toLocaleDateString('en-US', { weekday: 'long' });
    if (!acc[day]) acc[day] = [];
    acc[day].push(apt);
    return acc;
  }, {} as Record<string, Appointment[]>);

  // Calculate statistics
  const totalAppointments = weekAppointments.length;
  const pendingAppointments = weekAppointments.filter(apt => apt.status === 'new').length;
  const confirmedAppointments = weekAppointments.filter(apt => apt.status === 'confirmed').length;
  const completedAppointments = weekAppointments.filter(apt => apt.status === 'completed').length;

  // Generate employee schedule for the week
  const employeeSchedule = employees.filter(emp => emp.is_active).map(employee => {
    const employeeAppointments = weekAppointments.filter(apt => apt.assigned_employee_id === employee.id);
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
            <CardTitle className="text-sm font-medium">Total Appointments</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{totalAppointments}</div>
            <p className="text-xs text-muted-foreground">This week</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Pending</CardTitle>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">{pendingAppointments}</div>
            <p className="text-xs text-muted-foreground">Awaiting confirmation</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Confirmed</CardTitle>
            <User className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">{confirmedAppointments}</div>
            <p className="text-xs text-muted-foreground">Ready to go</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Completed</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-gray-600">{completedAppointments}</div>
            <p className="text-xs text-muted-foreground">Finished</p>
          </CardContent>
        </Card>
      </div>

      {/* Weekly Schedule */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Daily Appointments */}
        <Card>
          <CardHeader>
            <CardTitle>This Week's Appointments</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].map(day => {
                const dayAppointments = appointmentsByDay[day] || [];
                return (
                  <div key={day} className="border-l-4 border-primary pl-4">
                    <div className="flex items-center justify-between mb-2">
                      <h4 className="font-medium">{day}</h4>
                      <Badge variant="outline">{dayAppointments.length} appointments</Badge>
                    </div>
                    {dayAppointments.length > 0 ? (
                      <div className="space-y-2">
                        {dayAppointments.slice(0, 3).map(apt => (
                          <div key={apt.id} className="text-sm flex items-center justify-between bg-muted/50 p-2 rounded">
                            <div>
                              <span className="font-medium">{apt.preferred_time}</span> - {apt.first_name} {apt.last_name}
                            </div>
                            <Badge className={getStatusColor(apt.status)} variant="secondary">
                              {apt.status}
                            </Badge>
                          </div>
                        ))}
                        {dayAppointments.length > 3 && (
                          <p className="text-xs text-muted-foreground">+{dayAppointments.length - 3} more</p>
                        )}
                      </div>
                    ) : (
                      <p className="text-sm text-muted-foreground">No appointments</p>
                    )}
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>

        {/* Employee Schedule */}
        <Card>
          <CardHeader>
            <CardTitle>Employee Schedule</CardTitle>
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
                      <p className="text-sm font-medium">{appointments.length} appointments</p>
                      <p className="text-xs text-muted-foreground">~{totalHours} hours</p>
                    </div>
                  </div>
                  
                  {appointments.length > 0 ? (
                    <div className="space-y-2">
                      {appointments.slice(0, 2).map(apt => (
                        <div key={apt.id} className="text-sm flex items-center gap-2 bg-muted/30 p-2 rounded">
                          <Calendar className="h-3 w-3" />
                          <span>{new Date(apt.preferred_date).toLocaleDateString()} {apt.preferred_time}</span>
                          {apt.service_location === 'mobile' && <MapPin className="h-3 w-3" />}
                        </div>
                      ))}
                      {appointments.length > 2 && (
                        <p className="text-xs text-muted-foreground pl-5">+{appointments.length - 2} more appointments</p>
                      )}
                    </div>
                  ) : (
                    <p className="text-sm text-muted-foreground">No appointments this week</p>
                  )}
                </div>
              ))}
              
              {employeeSchedule.length === 0 && (
                <p className="text-muted-foreground text-center py-4">No active employees found</p>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};