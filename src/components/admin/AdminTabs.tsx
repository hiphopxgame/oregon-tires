
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { MessagesTab } from './MessagesTab';
import { AppointmentsTab } from './AppointmentsTab';
import { CalendarTab } from './CalendarTab';
import { DashboardOverview } from './DashboardOverview';
import { EmailLogsView } from './EmailLogsView';
import { EmailTestPanel } from './EmailTestPanel';
import { UpcomingAppointmentsView } from './UpcomingAppointmentsView';
import { ContactMessage, Appointment } from '@/types/admin';

interface AdminTabsProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
}

export const AdminTabs = ({
  activeTab,
  setActiveTab,
  contactMessages,
  updateMessageStatus,
  appointments,
  updateAppointmentStatus,
  updateAppointmentAssignment
}: AdminTabsProps) => {
  return (
    <Tabs value={activeTab} onValueChange={setActiveTab}>
      <TabsList className="grid w-full grid-cols-7">
        <TabsTrigger value="overview">Overview</TabsTrigger>
        <TabsTrigger value="calendar">Calendar</TabsTrigger>
        <TabsTrigger value="upcoming">Upcoming</TabsTrigger>
        <TabsTrigger value="appointments">Appointments</TabsTrigger>
        <TabsTrigger value="messages">Messages</TabsTrigger>
        <TabsTrigger value="emails">Email Logs</TabsTrigger>
        <TabsTrigger value="test">Email Test</TabsTrigger>
      </TabsList>

      <TabsContent value="overview">
        <DashboardOverview appointments={appointments} />
      </TabsContent>

      <TabsContent value="calendar">
        <CalendarTab 
          appointments={appointments}
          updateAppointmentStatus={updateAppointmentStatus}
        />
      </TabsContent>

      <TabsContent value="upcoming">
        <UpcomingAppointmentsView appointments={appointments} />
      </TabsContent>

      <TabsContent value="appointments">
        <AppointmentsTab 
          appointments={appointments}
          updateAppointmentStatus={updateAppointmentStatus}
          updateAppointmentAssignment={updateAppointmentAssignment}
        />
      </TabsContent>

      <TabsContent value="messages">
        <MessagesTab 
          contactMessages={contactMessages}
          updateMessageStatus={updateMessageStatus}
        />
      </TabsContent>

      <TabsContent value="emails">
        <EmailLogsView />
      </TabsContent>

      <TabsContent value="test">
        <div className="flex justify-center p-6">
          <EmailTestPanel />
        </div>
      </TabsContent>
    </Tabs>
  );
};
