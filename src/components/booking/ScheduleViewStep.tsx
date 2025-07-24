
import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { supabase } from '@/integrations/supabase/client';
import { useEmailNotifications } from '@/hooks/useEmailNotifications';
import { toast } from '@/hooks/use-toast';
import { useLanguage } from '@/hooks/useLanguage';
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
  const { t } = useLanguage();
  
  // Get the proper translated service name
  const getServiceDisplayName = (serviceValue: string) => {
    const serviceMap: { [key: string]: string } = {
      'new-tires': t.booking.newTires,
      'used-tires': t.booking.usedTires,
      'mount-and-balance-tires': t.booking.mountAndBalanceTires,
      'tire-repair': t.booking.tireRepair,
      'oil-change': t.booking.oilChange,
      'front-or-back-brake-change': t.booking.frontOrBackBrakeChange,
      'full-brake-change': t.booking.fullBrakeChange,
      'tuneup': t.booking.tuneup,
      'alignment': t.booking.alignment,
      'mechanical-inspection-and-estimate': t.booking.mechanicalInspectionAndEstimate,
      'mobile-service': t.booking.mobileService,
      'roadside-assistance': t.booking.roadsideAssistance
    };
    return serviceMap[serviceValue] || serviceValue.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
  };
  
  const [selectedTime, setSelectedTime] = useState<string>('');
  const [submitting, setSubmitting] = useState(false);
  const [bookingComplete, setBookingComplete] = useState(false);
  const navigate = useNavigate();
  const { sendAppointmentEmail } = useEmailNotifications();

  const { timeSlots, loading, serviceDuration } = useScheduleAvailability({
    preferredDate: customerInfo.preferredDate,
    service: customerInfo.service
  });

  const handleBookAppointment = async () => {
    if (!selectedTime) {
      toast({
        title: t.booking.errorTitle,
        description: t.booking.errorSelectTime,
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

      const { data: appointmentData, error } = await supabase
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
          travel_distance_miles: customerInfo.travel_distance_miles ? parseFloat(customerInfo.travel_distance_miles) : null,
          travel_cost_estimate: customerInfo.travel_cost_estimate ? parseFloat(customerInfo.travel_cost_estimate) : null,
          status: 'new',
          language: 'english'
        })
        .select('id')
        .single();

      if (error) throw error;

      // Send confirmation email to customer
      try {
        await sendAppointmentEmail('appointment_created', appointmentData.id);
      } catch (emailError) {
        console.error('Failed to send confirmation email:', emailError);
        // Don't fail the booking if email fails
      }

      setBookingComplete(true);
      
      toast({
        title: t.booking.successTitle,
        description: t.booking.successDesc,
        variant: "default",
      });
      
    } catch (error) {
      console.error('Error booking appointment:', error);
      toast({
        title: t.booking.errorTitle,
        description: t.booking.errorDesc,
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
        <p className="text-gray-600">{t.booking.loadingSchedule}</p>
      </div>
    );
  }

  if (isSunday) {
    return (
      <div className="text-center py-8">
        <AlertTriangle className="h-12 w-12 text-red-500 mx-auto mb-4" />
        <h3 className="text-xl font-semibold text-red-600 mb-2">{t.booking.closedSundays}</h3>
        <p className="text-gray-600">{t.booking.closedSundaysDesc}</p>
      </div>
    );
  }

  if (bookingComplete) {
    return (
      <div className="text-center py-12">
        <CheckCircle className="h-16 w-16 text-green-600 mx-auto mb-6" />
        <h2 className="text-3xl font-bold text-green-600 mb-4">{t.booking.appointmentConfirmed}</h2>
        <p className="text-lg text-gray-600 mb-2">
          {t.booking.thankYou}, {customerInfo.firstName}! {t.booking.appointmentScheduled}
        </p>
        <p className="text-gray-600 mb-6">
          {t.booking.contactSoon}
        </p>
        <div className="bg-green-50 border border-green-200 rounded-lg p-4 max-w-md mx-auto mb-8">
          <h3 className="font-semibold text-green-800 mb-2">{t.booking.appointmentDetails}:</h3>
          <p className="text-sm text-green-700">
            <strong>{t.booking.service}:</strong> {getServiceDisplayName(customerInfo.service)}
          </p>
          <p className="text-sm text-green-700">
            <strong>{t.booking.date}:</strong> {selectedDate.toLocaleDateString()}
          </p>
          <p className="text-sm text-green-700">
            <strong>{t.booking.time}:</strong> {timeSlots.find(slot => slot.time === selectedTime)?.display}
          </p>
        </div>
        
        <div className="flex justify-center gap-4">
          <Button 
            onClick={() => navigate('/')}
            className="bg-[#007030] hover:bg-[#005a26] text-white flex items-center gap-2"
          >
            <Home className="h-4 w-4" />
            {t.booking.goToHome}
          </Button>
          <Button 
            onClick={() => window.location.reload()}
            variant="outline"
            className="border-[#007030] text-[#007030] hover:bg-[#007030] hover:text-white flex items-center gap-2"
          >
            <Calendar className="h-4 w-4" />
            {t.booking.bookAnother}
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
            {t.booking.availableSlots} {selectedDate.toDateString()}
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
