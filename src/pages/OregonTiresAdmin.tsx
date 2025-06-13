
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Calendar } from '@/components/ui/calendar';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { Calendar as CalendarIcon, MessageCircle } from 'lucide-react';
import { Link } from 'react-router-dom';

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
  const [activeTab, setActiveTab] = useState('appointments');

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
        .update({ status: status.toLowerCase() })
        .eq('id', id);

      if (error) throw error;

      setAppointments(prev => 
        prev.map(apt => apt.id === id ? { ...apt, status: status.toLowerCase() } : apt)
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
        .update({ status: status.toLowerCase() })
        .eq('id', id);

      if (error) throw error;

      setContactMessages(prev => 
        prev.map(msg => msg.id === id ? { ...msg, status: status.toLowerCase() } : msg)
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
    const normalizedStatus = status.toLowerCase();
    const variants = {
      new: { className: 'bg-[#FEE11A] text-black', text: 'New' },
      priority: { className: 'bg-red-500 text-white', text: 'Priority' },
      completed: { className: 'bg-[#007030] text-white', text: 'Completed' }
    } as const;

    const variant = variants[normalizedStatus as keyof typeof variants] || variants.new;
    return (
      <span className={`px-2 py-1 rounded text-xs font-medium ${variant.className}`}>
        {variant.text}
      </span>
    );
  };

  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return appointments.filter(apt => apt.preferred_date === dateStr);
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);

  // Get dates that have appointments for calendar highlighting
  const appointmentDates = appointments.map(apt => new Date(apt.preferred_date + 'T00:00:00'));

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
          <Link to="/" className="inline-block">
            <h1 className="text-3xl font-bold hover:text-yellow-200">Oregon Tires Management</h1>
          </Link>
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
                  modifiers={{
                    hasAppointment: appointmentDates
                  }}
                  modifiersStyles={{
                    hasAppointment: { backgroundColor: '#FEE11A', color: '#000', fontWeight: 'bold' }
                  }}
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
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Main Content with Tabs */}
          <div className="lg:col-span-2">
            <Tabs value={activeTab} onValueChange={setActiveTab}>
              <TabsList className="grid w-full grid-cols-2">
                <TabsTrigger value="appointments">Appointments</TabsTrigger>
                <TabsTrigger value="messages">Messages</TabsTrigger>
              </TabsList>

              {/* Appointments Tab */}
              <TabsContent value="appointments">
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
                                  <Select
                                    value={appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}
                                    onValueChange={(value) => updateAppointmentStatus(appointment.id, value)}
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
              </TabsContent>

              {/* Messages Tab */}
              <TabsContent value="messages">
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
                                    value={message.status.charAt(0).toUpperCase() + message.status.slice(1)}
                                    onValueChange={(value) => updateMessageStatus(message.id, value)}
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
              </TabsContent>
            </Tabs>
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
