
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { MessagesTab } from './MessagesTab';
import { ContactMessage } from '@/types/admin';

interface AdminTabsProps {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
}

export const AdminTabs = ({
  activeTab,
  setActiveTab,
  contactMessages,
  updateMessageStatus
}: AdminTabsProps) => {
  return (
    <Tabs value="messages" onValueChange={setActiveTab}>
      <TabsList className="grid w-full grid-cols-1">
        <TabsTrigger value="messages">Messages</TabsTrigger>
      </TabsList>

      <TabsContent value="messages">
        <MessagesTab 
          contactMessages={contactMessages}
          updateMessageStatus={updateMessageStatus}
        />
      </TabsContent>
    </Tabs>
  );
};
