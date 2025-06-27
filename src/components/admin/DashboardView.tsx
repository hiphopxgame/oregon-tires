
import { AdminCalendar } from './AdminCalendar';
import { AdminTabs } from './AdminTabs';
import { Appointment, ContactMessage } from '@/types/admin';

interface DashboardViewProps {
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  appointmentDates: Date[];
  selectedDateAppointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  activeTab: string;
  setActiveTab: (tab: string) => void;
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
  appointments: Appointment[];
}

export const DashboardView = ({
  selectedDate,
  setSelectedDate,
  appointmentDates,
  selectedDateAppointments,
  updateAppointmentStatus,
  activeTab,
  setActiveTab,
  contactMessages,
  updateMessageStatus,
  appointments
}: DashboardViewProps) => {
  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <AdminCalendar
        selectedDate={selectedDate}
        setSelectedDate={setSelectedDate}
        appointmentDates={appointmentDates}
        selectedDateAppointments={selectedDateAppointments}
        updateAppointmentStatus={updateAppointmentStatus}
      />

      <div className="lg:col-span-2">
        {activeTab === 'calendar' ? (
          <div className="space-y-6">
            <h2 className="text-2xl font-bold text-[#007030]">Calendar View</h2>
            <p className="text-gray-600">
              Select a date from the calendar to view appointments for that day.
              The calendar shows highlighted dates with scheduled appointments.
            </p>
          </div>
        ) : (
          <AdminTabs
            activeTab={activeTab}
            setActiveTab={setActiveTab}
            contactMessages={contactMessages}
            updateMessageStatus={updateMessageStatus}
            appointments={appointments}
            updateAppointmentStatus={updateAppointmentStatus}
          />
        )}
      </div>
    </div>
  );
};
