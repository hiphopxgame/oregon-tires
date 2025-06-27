
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Appointment } from '@/types/admin';

interface AdminCalendarProps {
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  appointmentDates: Date[];
  selectedDateAppointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AdminCalendar = ({
  selectedDate,
  setSelectedDate,
  appointmentDates,
  selectedDateAppointments,
  updateAppointmentStatus
}: AdminCalendarProps) => {
  const getStatusBadge = (status: string) => {
    const normalizedStatus = status.toLowerCase();
    const variants = {
      new: { className: 'bg-[#FEE11A] text-black', text: 'New' },
      priority: { className: 'bg-red-500 text-white', text: 'Priority' },
      completed: { className: 'bg-[#007030] text-white', text: 'Completed' }
    } as const;

    const variant = variants[normalizedStatus as keyof typeof variants] || variants.new;
    return (
      <span className={`px-2 py-1 rounded text-xs font-medium ${variant.className}`}>
        {variant.text}
      </span>
    );
  };

  const capitalizeStatus = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
  };

  return (
    <div className="lg:col-span-1">
      <Card className="border-2" style={{ borderColor: '#007030' }}>
        <CardHeader style={{ backgroundColor: '#007030' }} className="text-white">
          <CardTitle>Appointment Calendar</CardTitle>
          <CardDescription className="text-white/80">
            {selectedDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
          </CardDescription>
        </CardHeader>
        <CardContent className="p-4">
          <Calendar
            mode="single"
            selected={selectedDate}
            onSelect={(date) => date && setSelectedDate(date)}
            className="w-full"
            modifiers={{
              hasAppointment: appointmentDates
            }}
            modifiersStyles={{
              hasAppointment: { backgroundColor: '#FEE11A', color: '#000', fontWeight: 'bold' }
            }}
          />
          
          <div className="mt-4 p-4 bg-gray-50 rounded-lg">
            <h3 className="font-semibold text-[#007030] mb-2">
              Appointments for {selectedDate.toLocaleDateString()}
            </h3>
            <div className="text-2xl font-bold text-[#007030] mb-1">
              {selectedDateAppointments.length} appointments
            </div>
            {selectedDateAppointments.length === 0 ? (
              <p className="text-gray-500 text-sm">No appointments scheduled for this date</p>
            ) : (
              <div className="space-y-2">
                {selectedDateAppointments.map((apt) => (
                  <div key={apt.id} className="text-sm p-2 bg-white rounded border-l-4" style={{ borderLeftColor: '#007030' }}>
                    <div className="font-medium">{apt.first_name} {apt.last_name}</div>
                    <div className="text-gray-600">{apt.service} - {apt.preferred_time}</div>
                    <div className="flex items-center gap-2 mt-1">
                      {getStatusBadge(apt.status)}
                      <Select
                        value={capitalizeStatus(apt.status)}
                        onValueChange={(value) => updateAppointmentStatus(apt.id, value.toLowerCase())}
                      >
                        <SelectTrigger className="w-24 h-6 text-xs">
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
                ))}
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};
