
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';

const OregonTires = () => {
  const { toast } = useToast();
  
  // Appointment form state
  const [appointmentForm, setAppointmentForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    service: '',
    preferred_date: '',
    preferred_time: '',
    message: '',
    language: 'english'
  });

  // Contact form state
  const [contactForm, setContactForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    message: '',
    language: 'english'
  });

  const [isSubmittingAppointment, setIsSubmittingAppointment] = useState(false);
  const [isSubmittingContact, setIsSubmittingContact] = useState(false);

  const services = [
    'Tire Installation',
    'Wheel Alignment',
    'Tire Rotation',
    'Brake Service',
    'Oil Change',
    'General Inspection'
  ];

  const handleAppointmentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmittingAppointment(true);

    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .insert([appointmentForm]);

      if (error) throw error;

      toast({
        title: "Appointment Requested",
        description: "We'll contact you soon to confirm your appointment.",
      });

      setAppointmentForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        service: '',
        preferred_date: '',
        preferred_time: '',
        message: '',
        language: 'english'
      });
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to submit appointment request. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsSubmittingAppointment(false);
    }
  };

  const handleContactSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmittingContact(true);

    try {
      const { error } = await supabase
        .from('oregon_tires_contact_messages')
        .insert([contactForm]);

      if (error) throw error;

      toast({
        title: "Message Sent",
        description: "Thank you for contacting us. We'll get back to you soon.",
      });

      setContactForm({
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        message: '',
        language: 'english'
      });
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to send message. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsSubmittingContact(false);
    }
  };

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="bg-primary text-primary-foreground py-6">
        <div className="container mx-auto px-4">
          <h1 className="text-4xl font-bold text-center">Oregon Tire & Wheels</h1>
          <p className="text-center mt-2 text-lg">Professional Tire Services & Quality Wheels</p>
        </div>
      </header>

      {/* Hero Section */}
      <section className="py-12 bg-muted/50">
        <div className="container mx-auto px-4 text-center">
          <h2 className="text-3xl font-bold mb-4">Expert Tire & Wheel Services</h2>
          <p className="text-xl text-muted-foreground mb-8">
            Quality service, competitive prices, and professional installation
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            {services.map((service, index) => (
              <Badge key={index} variant="secondary" className="text-sm px-3 py-1">
                {service}
              </Badge>
            ))}
          </div>
        </div>
      </section>

      <div className="container mx-auto px-4 py-12">
        <div className="grid md:grid-cols-2 gap-8">
          {/* Appointment Booking */}
          <Card>
            <CardHeader>
              <CardTitle>Schedule an Appointment</CardTitle>
              <CardDescription>
                Book your tire service appointment online
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleAppointmentSubmit} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="apt-first-name">First Name</Label>
                    <Input
                      id="apt-first-name"
                      value={appointmentForm.first_name}
                      onChange={(e) => setAppointmentForm({...appointmentForm, first_name: e.target.value})}
                      required
                    />
                  </div>
                  <div>
                    <Label htmlFor="apt-last-name">Last Name</Label>
                    <Input
                      id="apt-last-name"
                      value={appointmentForm.last_name}
                      onChange={(e) => setAppointmentForm({...appointmentForm, last_name: e.target.value})}
                      required
                    />
                  </div>
                </div>

                <div>
                  <Label htmlFor="apt-email">Email</Label>
                  <Input
                    id="apt-email"
                    type="email"
                    value={appointmentForm.email}
                    onChange={(e) => setAppointmentForm({...appointmentForm, email: e.target.value})}
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="apt-phone">Phone</Label>
                  <Input
                    id="apt-phone"
                    type="tel"
                    value={appointmentForm.phone}
                    onChange={(e) => setAppointmentForm({...appointmentForm, phone: e.target.value})}
                  />
                </div>

                <div>
                  <Label htmlFor="apt-service">Service Needed</Label>
                  <select
                    id="apt-service"
                    className="w-full px-3 py-2 border border-input bg-background rounded-md"
                    value={appointmentForm.service}
                    onChange={(e) => setAppointmentForm({...appointmentForm, service: e.target.value})}
                    required
                  >
                    <option value="">Select a service</option>
                    {services.map((service, index) => (
                      <option key={index} value={service}>{service}</option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="apt-date">Preferred Date</Label>
                    <Input
                      id="apt-date"
                      type="date"
                      value={appointmentForm.preferred_date}
                      onChange={(e) => setAppointmentForm({...appointmentForm, preferred_date: e.target.value})}
                      required
                    />
                  </div>
                  <div>
                    <Label htmlFor="apt-time">Preferred Time</Label>
                    <Input
                      id="apt-time"
                      type="time"
                      value={appointmentForm.preferred_time}
                      onChange={(e) => setAppointmentForm({...appointmentForm, preferred_time: e.target.value})}
                      required
                    />
                  </div>
                </div>

                <div>
                  <Label htmlFor="apt-message">Additional Information</Label>
                  <textarea
                    id="apt-message"
                    className="w-full px-3 py-2 border border-input bg-background rounded-md min-h-[100px]"
                    value={appointmentForm.message}
                    onChange={(e) => setAppointmentForm({...appointmentForm, message: e.target.value})}
                    placeholder="Tell us about your vehicle or specific needs..."
                  />
                </div>

                <Button type="submit" className="w-full" disabled={isSubmittingAppointment}>
                  {isSubmittingAppointment ? 'Scheduling...' : 'Schedule Appointment'}
                </Button>
              </form>
            </CardContent>
          </Card>

          {/* Contact Form */}
          <Card>
            <CardHeader>
              <CardTitle>Contact Us</CardTitle>
              <CardDescription>
                Get in touch for questions or quotes
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleContactSubmit} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="contact-first-name">First Name</Label>
                    <Input
                      id="contact-first-name"
                      value={contactForm.first_name}
                      onChange={(e) => setContactForm({...contactForm, first_name: e.target.value})}
                      required
                    />
                  </div>
                  <div>
                    <Label htmlFor="contact-last-name">Last Name</Label>
                    <Input
                      id="contact-last-name"
                      value={contactForm.last_name}
                      onChange={(e) => setContactForm({...contactForm, last_name: e.target.value})}
                      required
                    />
                  </div>
                </div>

                <div>
                  <Label htmlFor="contact-email">Email</Label>
                  <Input
                    id="contact-email"
                    type="email"
                    value={contactForm.email}
                    onChange={(e) => setContactForm({...contactForm, email: e.target.value})}
                    required
                  />
                </div>

                <div>
                  <Label htmlFor="contact-phone">Phone</Label>
                  <Input
                    id="contact-phone"
                    type="tel"
                    value={contactForm.phone}
                    onChange={(e) => setContactForm({...contactForm, phone: e.target.value})}
                  />
                </div>

                <div>
                  <Label htmlFor="contact-message">Message</Label>
                  <textarea
                    id="contact-message"
                    className="w-full px-3 py-2 border border-input bg-background rounded-md min-h-[120px]"
                    value={contactForm.message}
                    onChange={(e) => setContactForm({...contactForm, message: e.target.value})}
                    placeholder="How can we help you?"
                    required
                  />
                </div>

                <Button type="submit" className="w-full" disabled={isSubmittingContact}>
                  {isSubmittingContact ? 'Sending...' : 'Send Message'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>

        <Separator className="my-12" />

        {/* Services Section */}
        <section className="text-center">
          <h3 className="text-2xl font-bold mb-8">Our Services</h3>
          <div className="grid md:grid-cols-3 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Tire Installation</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Professional tire mounting, balancing, and installation for all vehicle types.
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader>
                <CardTitle>Wheel Services</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Wheel alignment, balancing, and custom wheel installation services.
                </p>
              </CardContent>
            </Card>
            <Card>
              <CardHeader>
                <CardTitle>Vehicle Maintenance</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Complete automotive services including brakes, oil changes, and inspections.
                </p>
              </CardContent>
            </Card>
          </div>
        </section>
      </div>

      {/* Footer */}
      <footer className="bg-muted py-8 mt-12">
        <div className="container mx-auto px-4 text-center">
          <p className="text-muted-foreground">
            © 2024 Oregon Tire & Wheels. Professional tire and automotive services.
          </p>
        </div>
      </footer>
    </div>
  );
};

export default OregonTires;
