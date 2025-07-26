
import { useState } from 'react';
import { Appointment } from '@/types/admin';

export const useAdminView = (appointments: Appointment[]) => {
  // Initialize with the first appointment date if available, otherwise today
  const getInitialDate = () => {
    if (appointments.length > 0) {
      const firstAppointmentDate = appointments[0].preferred_date.split(' ')[0]; // Take only the date part
      return new Date(firstAppointmentDate + 'T00:00:00');
    }
    return new Date();
  };
  
  const [selectedDate, setSelectedDate] = useState<Date>(getInitialDate());
  const [activeTab, setActiveTab] = useState('overview');
  const [currentView, setCurrentView] = useState('calendar');

  const getAppointmentsForDate = (date: Date) => {
    const dateStr = date.toISOString().split('T')[0];
    return appointments.filter(apt => {
      const aptDateStr = apt.preferred_date.split(' ')[0]; // Take only the date part
      return aptDateStr === dateStr;
    });
  };

  const selectedDateAppointments = getAppointmentsForDate(selectedDate);
  
  // Create appointment dates with proper date normalization
  const appointmentDates = appointments.map(apt => {
    // Handle both date formats: 'YYYY-MM-DD' and 'YYYY-MM-DD HH:MM:SS'
    const dateStr = apt.preferred_date.split(' ')[0]; // Take only the date part
    return new Date(dateStr + 'T00:00:00');
  });

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
