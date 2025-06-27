
import { useState } from 'react';
import { Appointment } from '@/types/admin';

export const useAdminView = (appointments: Appointment[]) => {
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [activeTab, setActiveTab] = useState('calendar');
  const [currentView, setCurrentView] = useState('dashboard');

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
