
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import translations from '@/utils/translations';
import { AdminHeader } from '@/components/admin/AdminHeader';
import { AdminFooter } from '@/components/admin/AdminFooter';
import { DashboardView } from '@/components/admin/DashboardView';
import { AppointmentsView } from '@/components/admin/AppointmentsView';
import { MessagesView } from '@/components/admin/MessagesView';
import { AnalyticsView } from '@/components/admin/AnalyticsView';

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
  const [language, setLanguage] = useState('english');
  const [t, setT] = useState(translations['english']);
  const [currentView, setCurrentView] = useState('dashboard');

  const toggleLanguage = () => {
    const newLanguage = language === 'english' ? 'spanish' : 'english';
    setLanguage(newLanguage);
    setT(translations[newLanguage]);
  };

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
      console.log('Updating appointment status:', { id, status: status.toLowerCase() });
      
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
      console.error('Error updating appointment status:', error);
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
    }
  };

  const updateMessageStatus = async (id: string, status: string) => {
    try {
      console.log('Updating message status:', { id, status: status.toLowerCase() });
      
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
      console.error('Error updating message status:', error);
      toast({
        title: "Error",
        description: "Failed to update status",
        variant: "destructive",
      });
    }
  };

  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return appointments.filter(apt => apt.preferred_date === dateStr);
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);
  const appointmentDates = appointments.map(apt => new Date(apt.preferred_date + 'T00:00:00'));

  const renderCurrentView = () => {
    switch (currentView) {
      case 'dashboard':
        return (
          <DashboardView
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
            appointmentDates={appointmentDates}
            selectedDateAppointments={selectedDateAppointments}
            updateAppointmentStatus={updateAppointmentStatus}
            activeTab={activeTab}
            setActiveTab={setActiveTab}
            appointments={appointments}
            contactMessages={contactMessages}
            updateMessageStatus={updateMessageStatus}
          />
        );
      case 'appointments':
        return (
          <AppointmentsView
            appointments={appointments}
            updateAppointmentStatus={updateAppointmentStatus}
          />
        );
      case 'messages':
        return (
          <MessagesView
            contactMessages={contactMessages}
            updateMessageStatus={updateMessageStatus}
          />
        );
      case 'analytics':
        return (
          <AnalyticsView
            appointments={appointments}
            contactMessages={contactMessages}
          />
        );
      default:
        return (
          <DashboardView
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
            appointmentDates={appointmentDates}
            selectedDateAppointments={selectedDateAppointments}
            updateAppointmentStatus={updateAppointmentStatus}
            activeTab={activeTab}
            setActiveTab={setActiveTab}
            appointments={appointments}
            contactMessages={contactMessages}
            updateMessageStatus={updateMessageStatus}
          />
        );
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-[#007030]">Loading admin dashboard...</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-white text-black">
      <AdminHeader 
        language={language} 
        toggleLanguage={toggleLanguage}
        currentView={currentView}
        setCurrentView={setCurrentView}
      />

      <div className="container mx-auto px-4 py-8">
        {renderCurrentView()}
      </div>

      <AdminFooter language={language} toggleLanguage={toggleLanguage} />
    </div>
  );
};

export default OregonTiresAdmin;
