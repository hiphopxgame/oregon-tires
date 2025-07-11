import { supabase } from '@/integrations/supabase/client';

export const useEmailNotifications = () => {
  const sendAppointmentEmail = async (
    type: 'appointment_created' | 'appointment_assigned' | 'appointment_completed',
    appointmentId: string
  ) => {
    try {
      const { data, error } = await supabase.functions.invoke('send-appointment-emails', {
        body: {
          type,
          appointmentId
        }
      });

      if (error) {
        console.error('Email sending error:', error);
        throw error;
      }

      console.log('Email sent successfully:', data);
      return data;
    } catch (error) {
      console.error('Failed to send email:', error);
      throw error;
    }
  };

  return {
    sendAppointmentEmail
  };
};