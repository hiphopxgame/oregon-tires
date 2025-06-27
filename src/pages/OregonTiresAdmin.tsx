
import { useLanguage } from '@/hooks/useLanguage';
import { useAdminData } from '@/hooks/useAdminData';
import { useAdminView } from '@/hooks/useAdminView';
import { AdminHeader } from '@/components/admin/AdminHeader';
import { AdminFooter } from '@/components/admin/AdminFooter';
import { AdminCalendar } from '@/components/admin/AdminCalendar';
import { DayView } from '@/components/admin/DayView';
import { AnalyticsView } from '@/components/admin/AnalyticsView';
import { AppointmentsView } from '@/components/admin/AppointmentsView';
import { MessagesView } from '@/components/admin/MessagesView';

const OregonTiresAdmin = () => {
  const { language, toggleLanguage } = useLanguage();
  const {
    appointments,
    contactMessages,
    loading,
    updateAppointmentStatus,
    updateMessageStatus,
    refetchData
  } = useAdminData();
  
  const {
    selectedDate,
    setSelectedDate,
    activeTab,
    setActiveTab,
    currentView,
    setCurrentView,
    selectedDateAppointments,
    appointmentDates
  } = useAdminView(appointments);

  const renderCurrentView = () => {
    switch (currentView) {
      case 'calendar':
        return (
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {/* Calendar Selector - Left Side */}
            <div className="lg:col-span-1">
              <AdminCalendar
                selectedDate={selectedDate}
                setSelectedDate={setSelectedDate}
                appointmentDates={appointmentDates}
                selectedDateAppointments={selectedDateAppointments}
                updateAppointmentStatus={updateAppointmentStatus}
              />
            </div>
            
            {/* Day View - Right Side */}
            <div className="lg:col-span-3">
              <DayView
                appointments={appointments}
                selectedDate={selectedDate}
                updateAppointmentStatus={updateAppointmentStatus}
                onDataRefresh={refetchData}
              />
            </div>
          </div>
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
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div className="lg:col-span-1">
              <AdminCalendar
                selectedDate={selectedDate}
                setSelectedDate={setSelectedDate}
                appointmentDates={appointmentDates}
                selectedDateAppointments={selectedDateAppointments}
                updateAppointmentStatus={updateAppointmentStatus}
              />
            </div>
            <div className="lg:col-span-3">
              <DayView
                appointments={appointments}
                selectedDate={selectedDate}
                updateAppointmentStatus={updateAppointmentStatus}
                onDataRefresh={refetchData}
              />
            </div>
          </div>
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
