
import React from 'react';
import { Button } from '@/components/ui/button';
import { useLanguage } from '@/hooks/useLanguage';

interface TimeSlot {
  time: string;
  display: string;
  status: 'available' | 'unavailable';
  conflictCount: number;
  message?: string;
}

interface BookingConfirmationProps {
  selectedTime: string;
  timeSlots: TimeSlot[];
  submitting: boolean;
  onConfirm: () => void;
}

export const BookingConfirmation: React.FC<BookingConfirmationProps> = ({
  selectedTime,
  timeSlots,
  submitting,
  onConfirm
}) => {
  const { t } = useLanguage();
  
  if (!selectedTime) return null;

  const selectedSlot = timeSlots.find(slot => slot.time === selectedTime);

  return (
    <div className="mt-6 p-4 bg-blue-50 rounded-lg">
      <p className="text-sm mb-3">
        {t.booking.selectedTime}: <strong>{selectedSlot?.display}</strong>
      </p>
      <Button 
        onClick={onConfirm}
        disabled={submitting}
        className="bg-[#0C3B1B] hover:bg-[#083018]"
      >
        {submitting ? t.booking.booking : t.booking.confirmAppointment}
      </Button>
    </div>
  );
};
