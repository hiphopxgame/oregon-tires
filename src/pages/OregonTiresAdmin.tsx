
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';

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
  created_at: string;
}

const OregonTiresAdmin = () => {
  const { toast } = useToast();
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [contactMessages, setContactMessages] = useState<ContactMessage[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [appointmentsRes, contactRes] = await Promise.all([
        supabase
          .from('oregon_tires_appointments')
          .select('*')
          .order('created_at', { ascending: false }),
        supabase
          .from('oregon_tires_contact_messages')
          .select('*')
          .order('created_at', { ascending: false })
      ]);

      if (appointmentsRes.error) throw appointmentsRes.error;
      if (contactRes.error) throw contactRes.error;

      setAppointments(appointmentsRes.data || []);
      setContactMessages(contactRes.data || []);
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to load data",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const updateAppointmentStatus = async (id: string, status: string) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({ status })
        .eq('id', id);

      if (error) throw error;

      setAppointments(prev => 
        prev.map(apt => apt.id === id ? { ...apt, status } : apt)
      );

      toast({
        title: "Status Updated",
        description: "Appointment status has been updated.",
      });
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
    }
  };

  const updateMessageStatus = async (id: string, status: string) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_contact_messages')
        .update({ status })
        .eq('id', id);

      if (error) throw error;

      setContactMessages(prev => 
        prev.map(msg => msg.id === id ? { ...msg, status } : msg)
      );

      toast({
        title: "Status Updated",
        description: "Message status has been updated.",
      });
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
    }
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      pending: 'default',
      confirmed: 'secondary',
      completed: 'outline',
      cancelled: 'destructive',
      new: 'default',
      read: 'secondary',
      replied: 'outline'
    } as const;

    return (
      <Badge variant={variants[status as keyof typeof variants] || 'default'}>
        {status}
      </Badge>
    );
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <p>Loading admin dashboard...</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="bg-primary text-primary-foreground py-6">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold">Oregon Tire & Wheels - Admin Dashboard</h1>
          <p className="mt-2">Manage appointments and customer messages</p>
        </div>
      </header>

      <div className="container mx-auto px-4 py-8">
        <div className="grid gap-8">
          {/* Appointments Section */}
          <Card>
            <CardHeader>
              <CardTitle>Appointments ({appointments.length})</CardTitle>
              <CardDescription>
                Manage customer appointment requests
              </CardDescription>
            </CardHeader>
            <CardContent>
              {appointments.length === 0 ? (
                <p className="text-muted-foreground">No appointments found.</p>
              ) : (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Customer</TableHead>
                        <TableHead>Contact</TableHead>
                        <TableHead>Service</TableHead>
                        <TableHead>Date & Time</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {appointments.map((appointment) => (
                        <TableRow key={appointment.id}>
                          <TableCell>
                            <div>
                              <p className="font-medium">
                                {appointment.first_name} {appointment.last_name}
                              </p>
                              {appointment.message && (
                                <p className="text-sm text-muted-foreground truncate max-w-[200px]">
                                  {appointment.message}
                                </p>
                              )}
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="text-sm">
                              <p>{appointment.email}</p>
                              {appointment.phone && <p>{appointment.phone}</p>}
                            </div>
                          </TableCell>
                          <TableCell>{appointment.service}</TableCell>
                          <TableCell>
                            <div className="text-sm">
                              <p>{appointment.preferred_date}</p>
                              <p>{appointment.preferred_time}</p>
                            </div>
                          </TableCell>
                          <TableCell>
                            {getStatusBadge(appointment.status)}
                          </TableCell>
                          <TableCell>
                            <div className="flex gap-2">
                              {appointment.status === 'pending' && (
                                <Button
                                  size="sm"
                                  onClick={() => updateAppointmentStatus(appointment.id, 'confirmed')}
                                >
                                  Confirm
                                </Button>
                              )}
                              {appointment.status === 'confirmed' && (
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => updateAppointmentStatus(appointment.id, 'completed')}
                                >
                                  Complete
                                </Button>
                              )}
                              <Button
                                size="sm"
                                variant="destructive"
                                onClick={() => updateAppointmentStatus(appointment.id, 'cancelled')}
                              >
                                Cancel
                              </Button>
                            </div>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Contact Messages Section */}
          <Card>
            <CardHeader>
              <CardTitle>Contact Messages ({contactMessages.length})</CardTitle>
              <CardDescription>
                Customer inquiries and messages
              </CardDescription>
            </CardHeader>
            <CardContent>
              {contactMessages.length === 0 ? (
                <p className="text-muted-foreground">No messages found.</p>
              ) : (
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Customer</TableHead>
                        <TableHead>Contact</TableHead>
                        <TableHead>Message</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {contactMessages.map((message) => (
                        <TableRow key={message.id}>
                          <TableCell>
                            <p className="font-medium">
                              {message.first_name} {message.last_name}
                            </p>
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
                            {getStatusBadge(message.status)}
                          </TableCell>
                          <TableCell>
                            <div className="flex gap-2">
                              {message.status === 'new' && (
                                <Button
                                  size="sm"
                                  onClick={() => updateMessageStatus(message.id, 'read')}
                                >
                                  Mark Read
                                </Button>
                              )}
                              {message.status === 'read' && (
                                <Button
                                  size="sm"
                                  variant="outline"
                                  onClick={() => updateMessageStatus(message.id, 'replied')}
                                >
                                  Mark Replied
                                </Button>
                              )}
                            </div>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default OregonTiresAdmin;
