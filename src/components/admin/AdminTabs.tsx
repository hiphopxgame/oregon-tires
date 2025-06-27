
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { MessagesTab } from './MessagesTab';
import { AppointmentsTab } from './AppointmentsTab';
import { CalendarTab } from './CalendarTab';
import { ContactMessage, Appointment } from '@/types/admin';

interface AdminTabsProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AdminTabs = ({
  activeTab,
  setActiveTab,
  contactMessages,
  updateMessageStatus,
  appointments,
  updateAppointmentStatus
}: AdminTabsProps) => {
  return (
    <Tabs value={activeTab} onValueChange={setActiveTab}>
      <TabsList className="grid w-full grid-cols-3">
        <TabsTrigger value="messages">Messages</TabsTrigger>
        <TabsTrigger value="appointments">Appointments</TabsTrigger>
        <TabsTrigger value="calendar">Calendar</TabsTrigger>
      </TabsList>

      <TabsContent value="messages">
        <MessagesTab 
          contactMessages={contactMessages}
          updateMessageStatus={updateMessageStatus}
        />
      </TabsContent>

      <TabsContent value="appointments">
        <AppointmentsTab 
          appointments={appointments}
          updateAppointmentStatus={updateAppointmentStatus}
        />
      </TabsContent>

      <TabsContent value="calendar">
        <CalendarTab 
          appointments={appointments}
          updateAppointmentStatus={updateAppointmentStatus}
        />
      </TabsContent>
    </Tabs>
  );
};
