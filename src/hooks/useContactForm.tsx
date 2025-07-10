
import { useState } from 'react';
import { supabase } from "@/integrations/supabase/client";
import { toast } from "@/hooks/use-toast";

interface ContactFormData {
  firstName: string;
  lastName: string;
  phone: string;
  email: string;
  message: string;
  service: string;
  preferred_date: string;
  preferred_time: string;
  tireSize: string;
  licensePlate: string;
  vin: string;
}

export const useContactForm = (language: string, t: any) => {
  const [contactForm, setContactForm] = useState<ContactFormData>({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    message: '',
    service: '',
    preferred_date: '',
    preferred_time: '',
    tireSize: '',
    licensePlate: '',
    vin: ''
  });

  const handleContactSubmit = async (e: React.FormEvent, isScheduleMode: boolean) => {
    e.preventDefault();

    const formData = {
      first_name: contactForm.firstName,
      last_name: contactForm.lastName,
      phone: contactForm.phone,
      email: contactForm.email,
      message: contactForm.message,
      language: language,
      status: 'new'
    };

    try {
      if (isScheduleMode) {
        const appointmentData = {
          ...formData,
          service: contactForm.service,
          preferred_date: contactForm.preferred_date,
          preferred_time: contactForm.preferred_time,
          tire_size: contactForm.tireSize || null,
          license_plate: contactForm.licensePlate || null,
          vin: contactForm.vin || null,
          status: 'new'
        };

        const { error } = await supabase
          .from('oregon_tires_appointments')
          .insert(appointmentData);

        if (error) throw error;
        
        toast({
          title: "Appointment Scheduled!",
          description: t.formSuccess,
          variant: "default",
        });
      } else {
        const { error } = await supabase
          .from('oregon_tires_contact_messages')
          .insert(formData);

        if (error) throw error;
        
        toast({
          title: "Message Sent!",
          description: t.formSuccess,
          variant: "default",
        });
      }

      setContactForm({
        firstName: '',
        lastName: '',
        phone: '',
        email: '',
        message: '',
        service: '',
        preferred_date: '',
        preferred_time: '',
        tireSize: '',
        licensePlate: '',
        vin: ''
      });
    } catch (error) {
      console.error("Form submission error:", error);
      toast({
        title: "Error",
        description: t.formError,
        variant: "destructive",
      });
    }
  };

  return {
    contactForm,
    setContactForm,
    handleContactSubmit
  };
};
