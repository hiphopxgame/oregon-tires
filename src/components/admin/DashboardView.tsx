
import { AdminCalendar } from './AdminCalendar';
import { AdminTabs } from './AdminTabs';

interface Appointment {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  service: string;
  preferred_date: string;
  preferred_time: string;
  message: string;
  status: string;
  language: string;
  created_at: string;
}

interface ContactMessage {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  message: string;
  status: string;
  language: string;
  created_at: string;
}

interface DashboardViewProps {
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  appointmentDates: Date[];
  selectedDateAppointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  activeTab: string;
  setActiveTab: (tab: string) => void;
  appointments: Appointment[];
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
}

export const DashboardView = ({
  selectedDate,
  setSelectedDate,
  appointmentDates,
  selectedDateAppointments,
  updateAppointmentStatus,
  activeTab,
  setActiveTab,
  appointments,
  contactMessages,
  updateMessageStatus
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
        <AdminTabs
          activeTab={activeTab}
          setActiveTab={setActiveTab}
          appointments={appointments}
          contactMessages={contactMessages}
          updateAppointmentStatus={updateAppointmentStatus}
          updateMessageStatus={updateMessageStatus}
        />
      </div>
    </div>
  );
};
