
import { useState } from 'react';
import { Appointment } from '@/types/admin';

export const useAdminView = (appointments: Appointment[]) => {
  // Initialize with the first appointment date if available, otherwise today
  const getInitialDate = () => {
    if (appointments.length > 0) {
      const firstAppointmentDate = appointments[0].preferred_date;
      return new Date(firstAppointmentDate + 'T00:00:00');
    }
    return new Date();
  };
  
  const [selectedDate, setSelectedDate] = useState<Date>(getInitialDate());
  const [activeTab, setActiveTab] = useState('overview');
  const [currentView, setCurrentView] = useState('calendar');

  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return appointments.filter(apt => apt.preferred_date === dateStr);
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);
  const appointmentDates = appointments.map(apt => new Date(apt.preferred_date + 'T00:00:00'));

  return {
    selectedDate,
    setSelectedDate,
    activeTab,
    setActiveTab,
    currentView,
    setCurrentView,
    selectedDateAppointments,
    appointmentDates
  };
};
