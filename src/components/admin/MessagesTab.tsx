
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ContactMessage } from '@/types/admin';

interface MessagesTabProps {
  contactMessages: ContactMessage[];
  updateMessageStatus: (id: string, status: string) => void;
}

export const MessagesTab = ({ contactMessages, updateMessageStatus }: MessagesTabProps) => {
  const capitalizeStatus = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
  };

  return (
    <Card className="border-2" style={{ borderColor: '#007030' }}>
      <CardHeader style={{ backgroundColor: '#007030' }} className="text-white">
        <CardTitle>Contact Messages</CardTitle>
      </CardHeader>
      <CardContent className="p-0">
        {contactMessages.length === 0 ? (
          <p className="text-gray-500 p-6">No messages found.</p>
        ) : (
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow style={{ backgroundColor: '#FEE11A' }}>
                  <TableHead className="text-black font-semibold">Customer</TableHead>
                  <TableHead className="text-black font-semibold">Contact</TableHead>
                  <TableHead className="text-black font-semibold">Message</TableHead>
                  <TableHead className="text-black font-semibold">Date</TableHead>
                  <TableHead className="text-black font-semibold">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {contactMessages.map((message) => (
                  <TableRow key={message.id} className="hover:bg-gray-50">
                    <TableCell>
                      <div>
                        <p className="font-medium text-[#007030]">
                          {message.first_name} {message.last_name}
                        </p>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        <p>{message.email}</p>
                        {message.phone && <p>{message.phone}</p>}
                      </div>
                    </TableCell>
                    <TableCell>
                      <p className="text-sm max-w-[300px] truncate">
                        {message.message}
                      </p>
                    </TableCell>
                    <TableCell>
                      <p className="text-sm">
                        {new Date(message.created_at).toLocaleDateString()}
                      </p>
                    </TableCell>
                    <TableCell>
                      <Select
                        value={capitalizeStatus(message.status)}
                        onValueChange={(value) => updateMessageStatus(message.id, value.toLowerCase())}
                      >
                        <SelectTrigger className="w-32">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="New">New</SelectItem>
                          <SelectItem value="Priority">Priority</SelectItem>
                          <SelectItem value="Completed">Completed</SelectItem>
                        </SelectContent>
                      </Select>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        )}
      </CardContent>
    </Card>
  );
};
