import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { useEmailNotifications } from '@/hooks/useEmailNotifications';

interface UseAppointmentTimerProps {
  appointmentId: string;
  onAppointmentUpdated?: () => void;
}

export const useAppointmentTimer = ({ appointmentId, onAppointmentUpdated }: UseAppointmentTimerProps) => {
  const [isRunning, setIsRunning] = useState(false);
  const [startTime, setStartTime] = useState<Date | null>(null);
  const [elapsedTime, setElapsedTime] = useState(0);
  const [appointment, setAppointment] = useState<any>(null);
  const { toast } = useToast();
  const { sendAppointmentEmail } = useEmailNotifications();

  // Load appointment data to check existing timer state
  useEffect(() => {
    const loadAppointment = async () => {
      try {
        const { data, error } = await supabase
          .from('oregon_tires_appointments')
          .select('*')
          .eq('id', appointmentId)
          .single();

        if (error) throw error;
        
        setAppointment(data);
        
        // If appointment has started_at but not completed_at, it's currently running
        if (data.started_at && !data.completed_at) {
          const startedAt = new Date(data.started_at);
          setStartTime(startedAt);
          setIsRunning(true);
          setElapsedTime(Math.floor((Date.now() - startedAt.getTime()) / 1000));
        }
      } catch (error) {
        console.error('Error loading appointment:', error);
      }
    };

    loadAppointment();
  }, [appointmentId]);

  // Update elapsed time every second when timer is running
  useEffect(() => {
    let interval: NodeJS.Timeout;
    
    if (isRunning && startTime) {
      interval = setInterval(() => {
        setElapsedTime(Math.floor((Date.now() - startTime.getTime()) / 1000));
      }, 1000);
    }
    
    return () => clearInterval(interval);
  }, [isRunning, startTime]);

  const startTimer = async () => {
    // Check if appointment is confirmed and has employee assigned before allowing timer start
    if (!appointment || appointment.status !== 'confirmed') {
      toast({
        title: "Cannot Start Timer",
        description: "Appointment must be Confirmed before starting the timer",
        variant: "destructive",
      });
      return;
    }

    if (!appointment.assigned_employee_id) {
      toast({
        title: "Cannot Start Timer",
        description: "An employee must be assigned before starting the timer",
        variant: "destructive",
      });
      return;
    }

    try {
      const now = new Date();
      
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({
          started_at: now.toISOString(),
          status: 'confirmed'
        })
        .eq('id', appointmentId);

      if (error) throw error;

      setStartTime(now);
      setIsRunning(true);
      setElapsedTime(0);

      toast({
        title: "Timer Started",
        description: "Appointment timer has been started and status updated to Confirmed.",
      });

      if (onAppointmentUpdated) {
        onAppointmentUpdated();
      }
    } catch (error) {
      console.error('Error starting timer:', error);
      toast({
        title: "Error",
        description: "Failed to start appointment timer",
        variant: "destructive",
      });
    }
  };

  const endTimer = async () => {
    try {
      const completedAt = new Date();
      const durationMinutes = startTime ? Math.floor((completedAt.getTime() - startTime.getTime()) / (1000 * 60)) : 0;

      const { error } = await supabase
        .from('oregon_tires_appointments')
        .update({
          completed_at: completedAt.toISOString(),
          actual_duration_minutes: durationMinutes,
          status: 'completed'
        })
        .eq('id', appointmentId);

      if (error) throw error;

      setIsRunning(false);
      setStartTime(null);
      setElapsedTime(0);

      // Send completion email to customer
      try {
        await sendAppointmentEmail('appointment_completed', appointmentId);
      } catch (emailError) {
        console.error('Error sending completion email:', emailError);
        // Don't show error to user as the main operation succeeded
      }

      toast({
        title: "Timer Stopped",
        description: `Appointment completed in ${durationMinutes} minutes. Status updated to Completed.`,
      });

      if (onAppointmentUpdated) {
        onAppointmentUpdated();
      }
    } catch (error) {
      console.error('Error ending timer:', error);
      toast({
        title: "Error",
        description: "Failed to stop appointment timer",
        variant: "destructive",
      });
    }
  };

  const formatTime = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  return {
    isRunning,
    elapsedTime,
    formattedTime: formatTime(elapsedTime),
    startTimer,
    endTimer
  };
};