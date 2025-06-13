
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Calendar } from '@/components/ui/calendar';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { Calendar as CalendarIcon, MessageCircle, Clock, User } from 'lucide-react';

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

const OregonTiresAdmin = () => {
  const { toast } = useToast();
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [contactMessages, setContactMessages] = useState<ContactMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());

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
      console.error('Error fetching data:', error);
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
      pending: { className: 'bg-[#FEE11A] text-black', text: 'pending' },
      confirmed: { className: 'bg-[#007030] text-white', text: 'confirmed' },
      completed: { className: 'bg-gray-500 text-white', text: 'completed' },
      cancelled: { className: 'bg-red-500 text-white', text: 'cancelled' },
      rejected: { className: 'bg-red-500 text-white', text: 'rejected' },
      new: { className: 'bg-[#FEE11A] text-black', text: 'new' },
      read: { className: 'bg-[#007030] text-white', text: 'read' },
      replied: { className: 'bg-gray-500 text-white', text: 'replied' }
    } as const;

    const variant = variants[status as keyof typeof variants] || variants.pending;
    return (
      <span className={`px-2 py-1 rounded text-xs font-medium ${variant.className}`}>
        {variant.text}
      </span>
    );
  };

  const getLanguageFlag = (language: string) => {
    return language === 'spanish' ? '🇲🇽' : '🇺🇸';
  };

  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return appointments.filter(apt => apt.preferred_date === dateStr);
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-[#007030]">Loading admin dashboard...</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-white text-black">
      {/* Header */}
      <header style={{ backgroundColor: '#007030' }} className="text-white py-6 shadow-lg">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold">Oregon Tires Management</h1>
          <div className="mt-2 flex gap-6">
            <div className="flex items-center gap-2">
              <CalendarIcon className="h-5 w-5" />
              <span>Appointments ({appointments.length})</span>
            </div>
            <div className="flex items-center gap-2">
              <MessageCircle className="h-5 w-5" />
              <span>Messages ({contactMessages.length})</span>
            </div>
          </div>
        </div>
      </header>

      <div className="container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Calendar Section */}
          <div className="lg:col-span-1">
            <Card className="border-2" style={{ borderColor: '#007030' }}>
              <CardHeader style={{ backgroundColor: '#007030' }} className="text-white">
                <CardTitle>Appointment Calendar</CardTitle>
                <CardDescription className="text-white/80">
                  {selectedDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
                </CardDescription>
              </CardHeader>
              <CardContent className="p-4">
                <Calendar
                  mode="single"
                  selected={selectedDate}
                  onSelect={(date) => date && setSelectedDate(date)}
                  className="w-full"
                />
                
                <div className="mt-4 p-4 bg-gray-50 rounded-lg">
                  <h3 className="font-semibold text-[#007030] mb-2">
                    Appointments for {selectedDate.toLocaleDateString()}
                  </h3>
                  <div className="text-2xl font-bold text-[#007030] mb-1">
                    {selectedDateAppointments.length} appointments
                  </div>
                  {selectedDateAppointments.length === 0 ? (
                    <p className="text-gray-500 text-sm">No appointments scheduled for this date</p>
                  ) : (
                    <div className="space-y-2">
                      {selectedDateAppointments.map((apt) => (
                        <div key={apt.id} className="text-sm p-2 bg-white rounded border-l-4" style={{ borderLeftColor: '#007030' }}>
                          <div className="font-medium">{apt.first_name} {apt.last_name}</div>
                          <div className="text-gray-600">{apt.service} - {apt.preferred_time}</div>
                          <div className="flex items-center gap-2 mt-1">
                            {getStatusBadge(apt.status)}
                            <span>{getLanguageFlag(apt.language)}</span>
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Appointments Section */}
          <div className="lg:col-span-2">
            <Card className="border-2" style={{ borderColor: '#007030' }}>
              <CardHeader style={{ backgroundColor: '#007030' }} className="text-white">
                <CardTitle>Service Appointments</CardTitle>
              </CardHeader>
              <CardContent className="p-0">
                {appointments.length === 0 ? (
                  <p className="text-gray-500 p-6">No appointments found.</p>
                ) : (
                  <div className="overflow-x-auto">
                    <Table>
                      <TableHeader>
                        <TableRow style={{ backgroundColor: '#FEE11A' }}>
                          <TableHead className="text-black font-semibold">Customer</TableHead>
                          <TableHead className="text-black font-semibold">Service</TableHead>
                          <TableHead className="text-black font-semibold">Date & Time</TableHead>
                          <TableHead className="text-black font-semibold">Status</TableHead>
                          <TableHead className="text-black font-semibold">Language</TableHead>
                          <TableHead className="text-black font-semibold">Actions</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {appointments.map((appointment) => (
                          <TableRow key={appointment.id} className="hover:bg-gray-50">
                            <TableCell>
                              <div>
                                <p className="font-medium text-[#007030]">
                                  {appointment.first_name} {appointment.last_name}
                                </p>
                                <p className="text-sm text-gray-600">{appointment.phone}</p>
                                <p className="text-sm text-gray-600">{appointment.email}</p>
                                {appointment.message && (
                                  <p className="text-sm text-gray-500 mt-1 truncate max-w-[200px]">
                                    {appointment.message}
                                  </p>
                                )}
                              </div>
                            </TableCell>
                            <TableCell>
                              <span className="font-medium">{appointment.service}</span>
                            </TableCell>
                            <TableCell>
                              <div className="text-sm">
                                <p className="font-medium">{appointment.preferred_date}</p>
                                <p className="text-gray-600">{appointment.preferred_time}</p>
                              </div>
                            </TableCell>
                            <TableCell>
                              {getStatusBadge(appointment.status)}
                            </TableCell>
                            <TableCell>
                              <span className="text-lg">{getLanguageFlag(appointment.language)}</span>
                            </TableCell>
                            <TableCell>
                              <div className="flex gap-2">
                                {appointment.status === 'pending' && (
                                  <>
                                    <Button
                                      size="sm"
                                      style={{ backgroundColor: '#007030' }}
                                      className="text-white hover:opacity-90"
                                      onClick={() => updateAppointmentStatus(appointment.id, 'confirmed')}
                                    >
                                      Confirm
                                    </Button>
                                    <Button
                                      size="sm"
                                      variant="destructive"
                                      onClick={() => updateAppointmentStatus(appointment.id, 'rejected')}
                                    >
                                      Reject
                                    </Button>
                                  </>
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
                                  variant="outline"
                                  style={{ borderColor: '#007030', color: '#007030' }}
                                  className="hover:bg-[#007030] hover:text-white"
                                >
                                  View Details
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
            <Card className="mt-8 border-2" style={{ borderColor: '#007030' }}>
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
                          <TableHead className="text-black font-semibold">Actions</TableHead>
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
                                <span className="text-lg">{getLanguageFlag(message.language)}</span>
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
                              {getStatusBadge(message.status)}
                            </TableCell>
                            <TableCell>
                              <div className="flex gap-2">
                                {message.status === 'new' && (
                                  <Button
                                    size="sm"
                                    style={{ backgroundColor: '#007030' }}
                                    className="text-white hover:opacity-90"
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

      {/* Footer */}
      <footer style={{ backgroundColor: '#007030' }} className="text-white py-4 mt-12">
        <div className="container mx-auto px-4 text-center">
          <p>Oregon Tires Management System</p>
        </div>
      </footer>
    </div>
  );
};

export default OregonTiresAdmin;
