
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AppointmentsTab } from './AppointmentsTab';
import { MessagesTab } from './MessagesTab';

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

interface AdminTabsProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  appointments: Appointment[];
  contactMessages: ContactMessage[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateMessageStatus: (id: string, status: string) => void;
}

export const AdminTabs = ({
  activeTab,
  setActiveTab,
  appointments,
  contactMessages,
  updateAppointmentStatus,
  updateMessageStatus
}: AdminTabsProps) => {
  return (
    <Tabs value={activeTab} onValueChange={setActiveTab}>
      <TabsList className="grid w-full grid-cols-2">
        <TabsTrigger value="appointments">Appointments</TabsTrigger>
        <TabsTrigger value="messages">Messages</TabsTrigger>
      </TabsList>

      <TabsContent value="appointments">
        <AppointmentsTab 
          appointments={appointments}
          updateAppointmentStatus={updateAppointmentStatus}
        />
      </TabsContent>

      <TabsContent value="messages">
        <MessagesTab 
          contactMessages={contactMessages}
          updateMessageStatus={updateMessageStatus}
        />
      </TabsContent>
    </Tabs>
  );
};
