
import { useState } from 'react';
import { supabase } from "@/integrations/supabase/client";
import { toast } from "@/hooks/use-toast";

interface ContactFormData {
  firstName: string;
  lastName: string;
  phone: string;
  email: string;
  message: string;
}

export const useContactForm = (language: string, t: any) => {
  const [contactForm, setContactForm] = useState<ContactFormData>({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    message: ''
  });

  const handleContactSubmit = async (e: React.FormEvent) => {
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
      const { error } = await supabase
        .from('oregon_tires_contact_messages')
        .insert(formData);

      if (error) throw error;
      
      toast({
        title: "Message Sent!",
        description: t.formSuccess,
        variant: "default",
      });

      setContactForm({
        firstName: '',
        lastName: '',
        phone: '',
        email: '',
        message: ''
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
