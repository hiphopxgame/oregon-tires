
import { useLanguage } from '@/hooks/useLanguage';
import { useAdminData } from '@/hooks/useAdminData';
import { useAdminView } from '@/hooks/useAdminView';
import { AdminHeader } from '@/components/admin/AdminHeader';
import { AdminFooter } from '@/components/admin/AdminFooter';
import { DashboardView } from '@/components/admin/DashboardView';
import { DayView } from '@/components/admin/DayView';
import { AnalyticsView } from '@/components/admin/AnalyticsView';

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
          <div className="space-y-6">
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-bold text-[#007030]">Appointments</h2>
              <input
                type="date"
                value={selectedDate.toISOString().split('T')[0]}
                onChange={(e) => setSelectedDate(new Date(e.target.value + 'T00:00:00'))}
                className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#007030] focus:border-transparent"
              />
            </div>
            <DayView
              appointments={appointments}
              selectedDate={selectedDate}
              updateAppointmentStatus={updateAppointmentStatus}
              onDataRefresh={refetchData}
            />
          </div>
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
