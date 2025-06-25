
import React from 'react';
import { Button } from '@/components/ui/button';

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
  if (!selectedTime) return null;

  const selectedSlot = timeSlots.find(slot => slot.time === selectedTime);

  return (
    <div className="mt-6 p-4 bg-blue-50 rounded-lg">
      <p className="text-sm mb-3">
        Selected time: <strong>{selectedSlot?.display}</strong>
      </p>
      <Button 
        onClick={onConfirm}
        disabled={submitting}
        className="bg-[#0C3B1B] hover:bg-[#083018]"
      >
        {submitting ? 'Booking...' : 'Confirm Appointment'}
      </Button>
    </div>
  );
};
