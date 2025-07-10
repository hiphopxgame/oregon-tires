
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { supabase } from '@/integrations/supabase/client';
import { toast } from '@/hooks/use-toast';
import { Clock, AlertTriangle, CheckCircle, Home, Calendar } from 'lucide-react';
import { useScheduleAvailability } from '@/hooks/useScheduleAvailability';
import { BookingSummary } from './BookingSummary';
import { TimeSlotGrid } from './TimeSlotGrid';
import { BookingConfirmation } from './BookingConfirmation';
import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';

interface ScheduleViewStepProps {
  customerInfo: CustomerInfo;
}

export const ScheduleViewStep: React.FC<ScheduleViewStepProps> = ({ customerInfo }) => {
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);
  const [bookingComplete, setBookingComplete] = useState(false);
  const navigate = useNavigate();

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
      // First, check if vehicle exists or create new one
      let vehicleId = null;
      
      if (customerInfo.vehicleMake || customerInfo.vehicleModel || customerInfo.vehicleYear || customerInfo.licensePlate || customerInfo.vin) {
        // Check if vehicle already exists
        const { data: existingVehicle } = await supabase
          .from('customer_vehicles')
          .select('id')
          .eq('customer_email', customerInfo.email)
          .eq('license_plate', customerInfo.licensePlate || '')
          .eq('vin', customerInfo.vin || '')
          .single();

        if (existingVehicle) {
          vehicleId = existingVehicle.id;
        } else {
          // Create new vehicle record
          const { data: newVehicle, error: vehicleError } = await supabase
            .from('customer_vehicles')
            .insert({
              customer_email: customerInfo.email,
              customer_name: `${customerInfo.firstName} ${customerInfo.lastName}`,
              make: customerInfo.vehicleMake || null,
              model: customerInfo.vehicleModel || null,
              year: customerInfo.vehicleYear ? parseInt(customerInfo.vehicleYear) : null,
              license_plate: customerInfo.licensePlate || null,
              vin: customerInfo.vin || null
            })
            .select('id')
            .single();

          if (vehicleError) throw vehicleError;
          vehicleId = newVehicle.id;
        }
      }

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
          message: customerInfo.message || null,
          tire_size: customerInfo.tireSize || null,
          license_plate: customerInfo.licensePlate || null,
          vin: customerInfo.vin || null,
          customer_address: customerInfo.address || null,
          customer_city: customerInfo.city || null,
          customer_state: customerInfo.state || null,
          customer_zip: customerInfo.zip || null,
          service_location: (customerInfo.service === 'mobile-service' || customerInfo.service === 'roadside-assistance') ? 'customer-location' : 'shop',
          vehicle_id: vehicleId,
          status: 'new',
          language: 'english'
        });

      if (error) throw error;

      setBookingComplete(true);
      
      toast({
        title: "Success!",
        description: "Your appointment has been scheduled successfully.",
        variant: "default",
      });
      
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

  if (bookingComplete) {
    return (
      <div className="text-center py-12">
        <CheckCircle className="h-16 w-16 text-green-600 mx-auto mb-6" />
        <h2 className="text-3xl font-bold text-green-600 mb-4">Appointment Confirmed!</h2>
        <p className="text-lg text-gray-600 mb-2">
          Thank you, {customerInfo.firstName}! Your appointment has been successfully scheduled.
        </p>
        <p className="text-gray-600 mb-6">
          We'll contact you soon to confirm the details.
        </p>
        <div className="bg-green-50 border border-green-200 rounded-lg p-4 max-w-md mx-auto mb-8">
          <h3 className="font-semibold text-green-800 mb-2">Appointment Details:</h3>
          <p className="text-sm text-green-700">
            <strong>Service:</strong> {customerInfo.service.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}
          </p>
          <p className="text-sm text-green-700">
            <strong>Date:</strong> {selectedDate.toLocaleDateString()}
          </p>
          <p className="text-sm text-green-700">
            <strong>Time:</strong> {timeSlots.find(slot => slot.time === selectedTime)?.display}
          </p>
        </div>
        
        <div className="flex justify-center gap-4">
          <Button 
            onClick={() => navigate('/')}
            className="bg-[#007030] hover:bg-[#005a26] text-white flex items-center gap-2"
          >
            <Home className="h-4 w-4" />
            Go to Home
          </Button>
          <Button 
            onClick={() => window.location.reload()}
            variant="outline"
            className="border-[#007030] text-[#007030] hover:bg-[#007030] hover:text-white flex items-center gap-2"
          >
            <Calendar className="h-4 w-4" />
            Book Another Appointment
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <BookingSummary customerInfo={customerInfo} serviceDuration={serviceDuration} />

      {/* Booking confirmation above time slots */}
      <BookingConfirmation
        selectedTime={selectedTime}
        timeSlots={timeSlots}
        submitting={submitting}
        onConfirm={handleBookAppointment}
      />

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
        </CardContent>
      </Card>

      {/* Booking confirmation below time slots */}
      <BookingConfirmation
        selectedTime={selectedTime}
        timeSlots={timeSlots}
        submitting={submitting}
        onConfirm={handleBookAppointment}
      />
    </div>
  );
};
