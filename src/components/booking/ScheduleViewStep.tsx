
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';
import { Clock, AlertTriangle } from 'lucide-react';
import { useScheduleAvailability } from '@/hooks/useScheduleAvailability';
import { BookingSummary } from './BookingSummary';
import { TimeSlotGrid } from './TimeSlotGrid';
import { BookingConfirmation } from './BookingConfirmation';

interface ScheduleViewStepProps {
  customerInfo: CustomerInfo;
}

export const ScheduleViewStep: React.FC<ScheduleViewStepProps> = ({ customerInfo }) => {
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);

  const { timeSlots, loading, serviceDuration } = useScheduleAvailability({
    preferredDate: customerInfo.preferredDate,
    service: customerInfo.service
  });

  const handleBookAppointment = async () => {
    if (!selectedTime) {
      toast({
        title: "Error",
        description: "Please select a time slot",
        variant: "destructive",
      });
      return;
    }

    setSubmitting(true);
    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .insert({
          first_name: customerInfo.firstName,
          last_name: customerInfo.lastName,
          phone: customerInfo.phone,
          email: customerInfo.email,
          service: customerInfo.service,
          preferred_date: customerInfo.preferredDate,
          preferred_time: selectedTime,
          status: 'pending',
          language: 'english'
        });

      if (error) throw error;

      toast({
        title: "Success!",
        description: "Your appointment has been scheduled successfully.",
        variant: "default",
      });

      setSelectedTime('');
      
    } catch (error) {
      console.error('Error booking appointment:', error);
      toast({
        title: "Error",
        description: "Failed to book appointment. Please try again.",
        variant: "destructive",
      });
    } finally {
      setSubmitting(false);
    }
  };

  const selectedDate = new Date(customerInfo.preferredDate + 'T00:00:00');
  const isSunday = selectedDate.getDay() === 0;

  if (loading) {
    return (
      <div className="flex items-center justify-center py-8">
        <p className="text-gray-600">Loading schedule...</p>
      </div>
    );
  }

  if (isSunday) {
    return (
      <div className="text-center py-8">
        <AlertTriangle className="h-12 w-12 text-red-500 mx-auto mb-4" />
        <h3 className="text-xl font-semibold text-red-600 mb-2">Closed on Sundays</h3>
        <p className="text-gray-600">We are closed on Sundays. Please select a different date.</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <BookingSummary customerInfo={customerInfo} serviceDuration={serviceDuration} />

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Available Time Slots for {selectedDate.toDateString()}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <TimeSlotGrid 
            timeSlots={timeSlots}
            selectedTime={selectedTime}
            onTimeSelect={setSelectedTime}
          />

          <BookingConfirmation
            selectedTime={selectedTime}
            timeSlots={timeSlots}
            submitting={submitting}
            onConfirm={handleBookAppointment}
          />
        </CardContent>
      </Card>
    </div>
  );
};
