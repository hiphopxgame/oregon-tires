import { useState, useEffect } from 'react';
import { useCustomHours, CustomHours } from '@/hooks/useCustomHours';

export const useHoursEditor = (selectedDate: Date) => {
  const { getHoursForDate, updateCustomHours, deleteCustomHours } = useCustomHours();
  const [saving, setSaving] = useState(false);
  
  const dateStr = selectedDate.toISOString().split('T')[0];
  const currentHours = getHoursForDate(dateStr);
  
  const [isClosed, setIsClosed] = useState(currentHours.is_closed);
  const [openingTime, setOpeningTime] = useState(currentHours.opening_time || '07:00');
  const [closingTime, setClosingTime] = useState(currentHours.closing_time || '19:00');
  const [simultaneousBookings, setSimultaneousBookings] = useState(currentHours.simultaneous_bookings || 2);

  // Update form state when selectedDate changes
  useEffect(() => {
    const updatedHours = getHoursForDate(dateStr);
    setIsClosed(updatedHours.is_closed);
    setOpeningTime(updatedHours.opening_time || '07:00');
    setClosingTime(updatedHours.closing_time || '19:00');
    setSimultaneousBookings(updatedHours.simultaneous_bookings || 2);
  }, [dateStr]);

  const handleSave = async () => {
    try {
      setSaving(true);
      await updateCustomHours(dateStr, {
        is_closed: isClosed,
        opening_time: isClosed ? null : openingTime,
        closing_time: isClosed ? null : closingTime,
        simultaneous_bookings: simultaneousBookings,
      });
    } catch (error) {
      // Error handled in hook
    } finally {
      setSaving(false);
    }
  };

  const handleReset = async () => {
    try {
      setSaving(true);
      await deleteCustomHours(dateStr);
      
      // Reset to default values
      const dayOfWeek = selectedDate.getDay();
      if (dayOfWeek === 0) { // Sunday
        setIsClosed(true);
        setOpeningTime('07:00');
        setClosingTime('19:00');
      } else {
        setIsClosed(false);
        setOpeningTime('07:00');
        setClosingTime('19:00');
      }
      setSimultaneousBookings(2);
    } catch (error) {
      // Error handled in hook
    } finally {
      setSaving(false);
    }
  };

  const hasChanges = () => {
    return (
      isClosed !== currentHours.is_closed ||
      (!isClosed && openingTime !== (currentHours.opening_time || '07:00')) ||
      (!isClosed && closingTime !== (currentHours.closing_time || '19:00')) ||
      simultaneousBookings !== (currentHours.simultaneous_bookings || 2)
    );
  };

  const isCustomHours = currentHours.id !== '';

  return {
    isClosed,
    openingTime,
    closingTime,
    simultaneousBookings,
    saving,
    hasChanges: hasChanges(),
    isCustomHours,
    setIsClosed,
    setOpeningTime,
    setClosingTime,
    setSimultaneousBookings,
    handleSave,
    handleReset
  };
};