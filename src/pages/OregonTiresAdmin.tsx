
import { useLanguage } from '@/hooks/useLanguage';
import { useAdminData } from '@/hooks/useAdminData';
import { useAdminView } from '@/hooks/useAdminView';
import { useAdminAuth } from '@/hooks/useAdminAuth';
import { AdminHeader } from '@/components/admin/AdminHeader';
import { AdminFooter } from '@/components/admin/AdminFooter';
import { AdminCalendar } from '@/components/admin/AdminCalendar';
import { DayView } from '@/components/admin/DayView';
import { AnalyticsView } from '@/components/admin/AnalyticsView';
import { AppointmentsView } from '@/components/admin/AppointmentsView';
import { MessagesView } from '@/components/admin/MessagesView';
import { EmployeesView } from '@/components/admin/EmployeesView';
import { ExpandedCalendarView } from '@/components/admin/ExpandedCalendarView';
import { GalleryManager } from '@/components/admin/GalleryManager';
import { EmailLogsView } from '@/components/admin/EmailLogsView';
import { DashboardOverview } from '@/components/admin/DashboardOverview';
import { UpcomingAppointmentsView } from '@/components/admin/UpcomingAppointmentsView';
import ServiceImagesManager from '@/components/admin/ServiceImagesManager';

const OregonTiresAdmin = () => {
  const { language, toggleLanguage, t } = useLanguage();
  const { signOut } = useAdminAuth();
  const {
    appointments,
    contactMessages,
    loading,
    updateAppointmentStatus,
    updateAppointmentAssignment,
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
      case 'overview':
        return <DashboardOverview appointments={appointments} />;
      case 'calendar':
        return (
          <ExpandedCalendarView
            appointments={appointments}
            selectedDate={selectedDate}
            setSelectedDate={setSelectedDate}
            appointmentDates={appointmentDates}
            updateAppointmentStatus={updateAppointmentStatus}
            updateAppointmentAssignment={updateAppointmentAssignment}
            onDataRefresh={refetchData}
          />
        );
      case 'upcoming':
        return <UpcomingAppointmentsView appointments={appointments} />;
      case 'appointments':
        return (
          <AppointmentsView
            appointments={appointments}
            updateAppointmentStatus={updateAppointmentStatus}
            updateAppointmentAssignment={updateAppointmentAssignment}
          />
        );
      case 'messages':
        return (
          <MessagesView
            contactMessages={contactMessages}
            updateMessageStatus={updateMessageStatus}
          />
        );
      case 'emails':
        return <EmailLogsView />;
      case 'employees':
        return <EmployeesView />;
      case 'gallery':
        return <GalleryManager />;
      case 'images':
        return <ServiceImagesManager />;
      case 'analytics':
        return (
          <AnalyticsView
            appointments={appointments}
            contactMessages={contactMessages}
          />
        );
      default:
        return <DashboardOverview appointments={appointments} />;
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-white flex items-center justify-center">
        <p className="text-[#007030]">{t.admin.loadingDashboard}</p>
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
        t={t}
        onSignOut={signOut}
      />

      <div className="container mx-auto px-4 py-8">
        {renderCurrentView()}
      </div>

      <AdminFooter language={language} toggleLanguage={toggleLanguage} />
    </div>
  );
};

export default OregonTiresAdmin;
