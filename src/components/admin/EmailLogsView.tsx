import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Mail, Eye, Calendar, Clock, User } from 'lucide-react';
import { format } from 'date-fns';

interface EmailLog {
  id: string;
  email_type: string;
  recipient_email: string;
  recipient_name: string;
  recipient_type: 'customer' | 'employee';
  subject: string;
  body: string;
  appointment_id: string;
  sent_at: string;
  resend_message_id: string | null;
}

export const EmailLogsView = () => {
  const [emailLogs, setEmailLogs] = useState<EmailLog[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchEmailLogs();
  }, []);

  const fetchEmailLogs = async () => {
    try {
      const { data, error } = await supabase
        .from('oregon_tires_email_logs')
        .select('*')
        .order('sent_at', { ascending: false })
        .limit(50);

      if (error) {
        console.error('Error fetching email logs:', error);
      } else {
        setEmailLogs((data || []) as EmailLog[]);
      }
    } catch (error) {
      console.error('Error fetching email logs:', error);
    } finally {
      setLoading(false);
    }
  };

  const getEmailTypeColor = (type: string) => {
    switch (type) {
      case 'appointment_created':
        return 'bg-blue-100 text-blue-800';
      case 'appointment_assigned':
        return 'bg-yellow-100 text-yellow-800';
      case 'appointment_completed':
        return 'bg-green-100 text-green-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getEmailTypeLabel = (type: string) => {
    switch (type) {
      case 'appointment_created':
        return 'Confirmation';
      case 'appointment_assigned':
        return 'Assignment';
      case 'appointment_completed':
        return 'Completion';
      default:
        return type;
    }
  };

  const getRecipientTypeIcon = (type: 'customer' | 'employee') => {
    return type === 'customer' ? <User className="h-4 w-4" /> : <Mail className="h-4 w-4" />;
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-8">
        <div className="text-muted-foreground">Loading email logs...</div>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-2xl font-bold">Email Logs</h2>
        <Button onClick={fetchEmailLogs} variant="outline" size="sm">
          Refresh
        </Button>
      </div>

      {emailLogs.length === 0 ? (
        <Card>
          <CardContent className="py-8 text-center">
            <Mail className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
            <p className="text-muted-foreground">No emails have been sent yet.</p>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-3">
          {emailLogs.map((log) => (
            <Card key={log.id} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                  <div className="space-y-2">
                    <div className="flex items-center gap-2">
                      <Badge className={getEmailTypeColor(log.email_type)}>
                        {getEmailTypeLabel(log.email_type)}
                      </Badge>
                      <Badge variant="outline" className="flex items-center gap-1">
                        {getRecipientTypeIcon(log.recipient_type)}
                        {log.recipient_type}
                      </Badge>
                    </div>
                    <CardTitle className="text-lg">{log.subject}</CardTitle>
                  </div>
                  <Dialog>
                    <DialogTrigger asChild>
                      <Button variant="outline" size="sm">
                        <Eye className="h-4 w-4 mr-1" />
                        View
                      </Button>
                    </DialogTrigger>
                    <DialogContent className="max-w-2xl">
                      <DialogHeader>
                        <DialogTitle>{log.subject}</DialogTitle>
                      </DialogHeader>
                      <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4 text-sm">
                          <div>
                            <strong>To:</strong> {log.recipient_name} ({log.recipient_email})
                          </div>
                          <div>
                            <strong>Type:</strong> {getEmailTypeLabel(log.email_type)}
                          </div>
                          <div>
                            <strong>Sent:</strong> {format(new Date(log.sent_at), 'PPpp')}
                          </div>
                          <div>
                            <strong>Message ID:</strong> {log.resend_message_id || 'N/A'}
                          </div>
                        </div>
                        <div>
                          <strong>Email Content:</strong>
                          <ScrollArea className="h-96 mt-2 border rounded p-4">
                            <div 
                              dangerouslySetInnerHTML={{ __html: log.body }}
                              className="prose prose-sm max-w-none"
                            />
                          </ScrollArea>
                        </div>
                      </div>
                    </DialogContent>
                  </Dialog>
                </div>
              </CardHeader>
              <CardContent className="pt-0">
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                  <div className="flex items-center gap-4">
                    <div className="flex items-center gap-1">
                      <User className="h-4 w-4" />
                      {log.recipient_name}
                    </div>
                    <div className="flex items-center gap-1">
                      <Mail className="h-4 w-4" />
                      {log.recipient_email}
                    </div>
                  </div>
                  <div className="flex items-center gap-1">
                    <Calendar className="h-4 w-4" />
                    {format(new Date(log.sent_at), 'MMM dd')}
                    <Clock className="h-4 w-4 ml-2" />
                    {format(new Date(log.sent_at), 'HH:mm')}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
};