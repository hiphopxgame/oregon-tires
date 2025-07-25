import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Appointment } from '@/types/admin';
import { useLanguage } from '@/hooks/useLanguage';
import { format, addDays, startOfDay, isAfter, isBefore } from 'date-fns';
import { CalendarX, Clock, MapPin, User, Phone, Mail } from 'lucide-react';

interface UpcomingAppointmentsViewProps {
  appointments: Appointment[];
}

export const UpcomingAppointmentsView = ({ appointments }: UpcomingAppointmentsViewProps) => {
  const { t } = useLanguage();

  // Get the next 3 days starting from today
  const today = startOfDay(new Date());
  const next3Days = [
    today,
    addDays(today, 1),
    addDays(today, 2)
  ];

  // Filter appointments for the next 3 days
  const upcomingAppointments = appointments.filter(apt => {
    const aptDate = new Date(apt.preferred_date + 'T00:00:00');
    const endOfThirdDay = addDays(today, 3);
    return isAfter(aptDate, addDays(today, -1)) && isBefore(aptDate, endOfThirdDay);
  });

  // Group appointments by date
  const appointmentsByDate = next3Days.map(date => {
    const dateStr = format(date, 'yyyy-MM-dd');
    const dayAppointments = upcomingAppointments.filter(apt => apt.preferred_date === dateStr);
    return {
      date,
      dateStr,
      appointments: dayAppointments
    };
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'confirmed': return 'bg-green-100 text-green-800';
      case 'completed': return 'bg-blue-100 text-blue-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      case 'in-progress': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const formatService = (service: string) => {
    return service.split('-').map(word => 
      word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
  };

  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
        <div className="bg-green-700 text-white px-6 py-4">
          <h2 className="text-2xl font-bold">{t.admin.upcomingAppointments}</h2>
          <p className="text-green-100">{t.admin.next3DaysOverview}</p>
        </div>
        
        <div className="p-6">
          <div className="grid gap-6">
            {appointmentsByDate.map(({ date, appointments: dayAppointments }) => (
              <Card key={date.toISOString()} className="border-l-4 border-l-green-600">
                <CardHeader className="pb-4">
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-lg">
                      {format(date, 'EEEE, MMMM d, yyyy')}
                    </CardTitle>
                    {dayAppointments.length === 0 ? (
                      <div className="flex items-center gap-2 text-orange-600">
                        <CalendarX className="h-5 w-5" />
                        <Badge variant="outline" className="border-orange-600 text-orange-600">
                          {t.admin.noAppointments}
                        </Badge>
                      </div>
                    ) : (
                      <Badge variant="secondary">
                        {dayAppointments.length} {t.admin.appointments}
                      </Badge>
                    )}
                  </div>
                </CardHeader>
                
                <CardContent>
                  {dayAppointments.length === 0 ? (
                    <div className="text-center py-8 text-gray-500">
                      <CalendarX className="h-12 w-12 mx-auto mb-3 text-gray-400" />
                      <p className="text-lg font-medium">{t.admin.nothingScheduled}</p>
                      <p className="text-sm">{t.admin.dayAvailableForBooking}</p>
                    </div>
                  ) : (
                    <div className="space-y-4">
                      {dayAppointments.map((appointment) => (
                        <div key={appointment.id} className="border rounded-lg p-4 hover:bg-gray-50">
                          <div className="flex items-start justify-between mb-3">
                            <div className="flex items-center gap-3">
                              <div className="flex items-center gap-1 text-sm text-gray-600">
                                <Clock className="h-4 w-4" />
                                {appointment.preferred_time}
                              </div>
                              <Badge className={getStatusColor(appointment.status)}>
                                {t.admin.status[appointment.status] || appointment.status}
                              </Badge>
                            </div>
                          </div>
                          
                          <div className="grid md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                              <div className="flex items-center gap-2">
                                <User className="h-4 w-4 text-gray-500" />
                                <span className="font-medium">
                                  {appointment.first_name} {appointment.last_name}
                                </span>
                              </div>
                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Phone className="h-4 w-4" />
                                {appointment.phone}
                              </div>
                              <div className="flex items-center gap-2 text-sm text-gray-600">
                                <Mail className="h-4 w-4" />
                                {appointment.email}
                              </div>
                            </div>
                            
                            <div className="space-y-2">
                              <div className="text-sm">
                                <strong>{t.admin.service}:</strong> {formatService(appointment.service)}
                              </div>
                              {appointment.service_location && (
                                <div className="flex items-center gap-2 text-sm text-gray-600">
                                  <MapPin className="h-4 w-4" />
                                  {appointment.service_location === 'mobile' 
                                    ? t.admin.mobileService 
                                    : t.admin.shopService}
                                </div>
                              )}
                              {appointment.tire_size && (
                                <div className="text-sm text-gray-600">
                                  <strong>{t.admin.tireSize}:</strong> {appointment.tire_size}
                                </div>
                              )}
                            </div>
                          </div>
                          
                          {appointment.message && (
                            <div className="mt-3 p-3 bg-gray-50 rounded text-sm">
                              <strong>{t.admin.customerMessage}:</strong> {appointment.message}
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  )}
                </CardContent>
              </Card>
            ))}
          </div>
          
          <div className="mt-6 p-4 bg-blue-50 rounded-lg">
            <h3 className="font-semibold text-blue-900 mb-2">{t.admin.summary}</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">{upcomingAppointments.length}</div>
                <div className="text-blue-700">{t.admin.appointments}</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">
                  {upcomingAppointments.filter(a => a.status === 'confirmed').length}
                </div>
                <div className="text-green-700">{t.admin.confirmed}</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-yellow-600">
                  {upcomingAppointments.filter(a => a.status === 'new').length}
                </div>
                <div className="text-yellow-700">{t.admin.pending}</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-orange-600">
                  {appointmentsByDate.filter(day => day.appointments.length === 0).length}
                </div>
                <div className="text-orange-700">{t.admin.emptyDays}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};