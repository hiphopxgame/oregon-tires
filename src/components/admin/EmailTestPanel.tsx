import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { supabase } from '@/integrations/supabase/client';
import { Mail, Loader2 } from 'lucide-react';

export const EmailTestPanel = () => {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const testSMTP = async () => {
    if (!email) {
      toast({
        title: "Error",
        description: "Please enter a valid email address",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);
    try {
      // Create a test appointment to send an email
      const { data: testAppointment, error: appointmentError } = await supabase
        .from('oregon_tires_appointments')
        .insert({
          first_name: 'Test',
          last_name: 'User',
          email: email,
          phone: '(555) 123-4567',
          service: 'SMTP Test',
          preferred_date: new Date().toISOString().split('T')[0],
          preferred_time: '10:00',
          message: 'This is a test email to verify SMTP configuration.',
          language: 'english',
          status: 'test'
        })
        .select('id')
        .single();

      if (appointmentError) throw appointmentError;

      // Send test email
      const { data, error } = await supabase.functions.invoke('send-appointment-emails', {
        body: {
          type: 'appointment_created',
          appointmentId: testAppointment.id
        }
      });

      if (error) throw error;

      // Clean up test appointment
      await supabase
        .from('oregon_tires_appointments')
        .delete()
        .eq('id', testAppointment.id);

      toast({
        title: "Test Email Sent!",
        description: `Test email sent successfully to ${email}. Check your inbox and spam folder.`,
      });

    } catch (error: any) {
      console.error('SMTP test failed:', error);
      toast({
        title: "SMTP Test Failed",
        description: error.message || "Failed to send test email. Check your SMTP configuration.",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card className="w-full max-w-md">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Mail className="h-5 w-5" />
          SMTP Test Panel
        </CardTitle>
        <CardDescription>
          Test your SMTP configuration by sending a test email
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="space-y-2">
          <Label htmlFor="test-email">Test Email Address</Label>
          <Input
            id="test-email"
            type="email"
            placeholder="your-email@example.com"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />
        </div>
        <Button 
          onClick={testSMTP} 
          disabled={loading || !email}
          className="w-full"
        >
          {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          Send Test Email
        </Button>
        <div className="text-sm text-muted-foreground">
          <p>This will send a test appointment confirmation email to verify your SMTP settings.</p>
        </div>
      </CardContent>
    </Card>
  );
};