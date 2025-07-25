
import { MessagesTab } from './MessagesTab';
import { ContactMessage } from '@/types/admin';
import { useLanguage } from '@/hooks/useLanguage';

interface MessagesViewProps {
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
}

export const MessagesView = ({ contactMessages, updateMessageStatus }: MessagesViewProps) => {
  const { t } = useLanguage();
  
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
        <div className="bg-green-700 text-white px-6 py-4">
          <h2 className="text-2xl font-bold">{t.admin.allMessages}</h2>
          <p className="text-green-100">{t.admin.manageAllMessages}</p>
        </div>
        <div className="p-6">
          <MessagesTab 
            contactMessages={contactMessages} 
            updateMessageStatus={updateMessageStatus} 
          />
        </div>
      </div>
    </div>
  );
};
