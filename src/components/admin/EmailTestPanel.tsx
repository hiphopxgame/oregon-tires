
import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { supabase } from '@/integrations/supabase/client';
import { Mail, Loader2, CheckCircle } from 'lucide-react';

export const EmailTestPanel = () => {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const testResend = async () => {
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
        .from('oretir_appointments')
        .insert({
          first_name: 'Test',
          last_name: 'Customer',
          email: email,
          phone: '(555) 123-4567',
          service: 'Email Test Service',
          preferred_date: new Date().toISOString().split('T')[0],
          preferred_time: '10:00',
          message: 'This is a test email to verify Resend integration with Oregon Tires.',
          language: 'english',
          status: 'test'
        })
        .select('id')
        .single();

      if (appointmentError) throw appointmentError;

      // Send test email using Resend
      const { data, error } = await supabase.functions.invoke('send-appointment-emails', {
        body: {
          type: 'appointment_created',
          appointmentId: testAppointment.id
        }
      });

      if (error) throw error;

      // Clean up test appointment
      await supabase
        .from('oretir_appointments')
        .delete()
        .eq('id', testAppointment.id);

      toast({
        title: "Test Email Sent Successfully!",
        description: `Test email sent via Resend to ${email}. Check your inbox and spam folder.`,
      });

    } catch (error: any) {
      console.error('Resend test failed:', error);
      toast({
        title: "Email Test Failed",
        description: error.message || "Failed to send test email via Resend. Please check your configuration.",
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
          Resend Email Test
        </CardTitle>
        <CardDescription>
          Test your Resend integration by sending a test appointment confirmation
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
          onClick={testResend} 
          disabled={loading || !email}
          className="w-full"
        >
          {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {!loading && <CheckCircle className="mr-2 h-4 w-4" />}
          Send Test Email via Resend
        </Button>
        <div className="text-sm text-muted-foreground space-y-2">
          <p>This will send a test appointment confirmation email using your verified oregon.tires domain.</p>
          <div className="bg-green-50 border border-green-200 rounded p-2">
            <p className="text-green-800 font-medium">✓ Domain Verified</p>
            <p className="text-green-700 text-xs">Your oregon.tires domain is verified with Resend</p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};
