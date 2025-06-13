
import React, { useState } from 'react';
import { Phone, MapPin, Clock, Star, Check, MessageCircle, Calendar } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { supabase } from "@/integrations/supabase/client";
import { toast } from "sonner";

const OregonTires = () => {
  const [contactForm, setContactForm] = useState({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    message: ''
  });

  const [appointmentForm, setAppointmentForm] = useState({
    name: '',
    phone: '',
    email: '',
    service: '',
    preferred_date: '',
    message: ''
  });

  const handleContactSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const { error } = await supabase
        .from('oregon_tires_contact_messages')
        .insert([{
          first_name: contactForm.firstName,
          last_name: contactForm.lastName,
          phone: contactForm.phone,
          email: contactForm.email,
          message: contactForm.message
        }]);

      if (error) throw error;

      toast.success("Message sent successfully! We'll get back to you soon.");
      setContactForm({
        firstName: '',
        lastName: '',
        phone: '',
        email: '',
        message: ''
      });
    } catch (error) {
      console.error('Error submitting contact form:', error);
      toast.error("Failed to send message. Please try again.");
    }
  };

  const handleAppointmentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const { error } = await supabase
        .from('oregon_tires_appointments')
        .insert([{
          name: appointmentForm.name,
          phone: appointmentForm.phone,
          email: appointmentForm.email,
          service: appointmentForm.service,
          preferred_date: appointmentForm.preferred_date,
          message: appointmentForm.message,
          status: 'pending'
        }]);

      if (error) throw error;

      toast.success("Appointment request submitted! We'll contact you to confirm.");
      setAppointmentForm({
        name: '',
        phone: '',
        email: '',
        service: '',
        preferred_date: '',
        message: ''
      });
    } catch (error) {
      console.error('Error submitting appointment:', error);
      toast.error("Failed to submit appointment. Please try again.");
    }
  };

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <header className="bg-white shadow-sm sticky top-0 z-50">
        <div className="container mx-auto px-4 py-3">
          {/* Top Bar */}
          <div className="flex flex-col lg:flex-row justify-between items-center mb-4 text-sm">
            <div className="flex flex-col sm:flex-row items-center gap-4 mb-2 lg:mb-0">
              <div className="flex items-center gap-1 text-gray-700">
                <Phone className="h-4 w-4" />
                (503) 367-9714
              </div>
              <div className="flex items-center gap-1 text-gray-700">
                <MapPin className="h-4 w-4" />
                8536 SE 82nd Ave, Portland, OR 97266
              </div>
              <div className="flex items-center gap-1 text-gray-700">
                <Clock className="h-4 w-4" />
                Mon-Sat: 7AM-7PM
              </div>
            </div>
            <div className="text-gray-700">
              🇺🇸 English
            </div>
          </div>

          {/* Main Header */}
          <div className="flex flex-col lg:flex-row justify-between items-center">
            <div className="flex items-center gap-4 mb-4 lg:mb-0">
              <img 
                src="/lovable-uploads/f000a232-32e4-4f91-8b69-f7e61ac811f2.png" 
                alt="Oregon Tires Logo" 
                className="h-16 w-16"
              />
              <div>
                <h1 className="text-2xl font-bold" style={{ color: '#007030' }}>Oregon Tires</h1>
                <p className="text-lg text-gray-600">High Quality Auto Care</p>
              </div>
            </div>

            {/* Navigation */}
            <nav className="flex flex-wrap items-center gap-6">
              <a href="#home" className="text-gray-700 hover:text-green-700 font-medium">Home</a>
              <a href="#services" className="text-gray-700 hover:text-green-700 font-medium">Services</a>
              <a href="#about" className="text-gray-700 hover:text-green-700 font-medium">About</a>
              <a href="#contact" className="text-gray-700 hover:text-green-700 font-medium">Contact</a>
              <Button 
                className="text-white font-medium"
                style={{ backgroundColor: '#007030' }}
                onClick={() => document.getElementById('appointment')?.scrollIntoView({ behavior: 'smooth' })}
              >
                Schedule Service
              </Button>
            </nav>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section id="home" className="py-20" style={{ backgroundColor: '#007030' }}>
        <div className="container mx-auto px-4 text-center">
          <div className="max-w-4xl mx-auto">
            <h2 className="text-5xl font-bold text-white mb-4">
              High Quality Auto Services<br />& Expert Care
            </h2>
            <p className="text-xl text-white mb-8">
              Your trusted automotive service center in Portland, Oregon. Quality tires, expert service, unbeatable prices.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button 
                size="lg" 
                className="text-black font-bold"
                style={{ backgroundColor: '#FEE11A' }}
                onClick={() => document.getElementById('contact')?.scrollIntoView({ behavior: 'smooth' })}
              >
                Contact Oregon Tires
              </Button>
              <Button 
                size="lg" 
                variant="outline" 
                className="text-white border-white hover:bg-white hover:text-green-700"
                onClick={() => document.getElementById('appointment')?.scrollIntoView({ behavior: 'smooth' })}
              >
                Book an Appointment
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Hero Image Section */}
      <section className="py-12">
        <div className="container mx-auto px-4">
          <div className="text-center mb-8">
            <img 
              src="/lovable-uploads/92683d6e-fdfc-4bcc-935d-357e68ebfc33.png" 
              alt="Oregon Tires Auto Care - Spanish & English Speaking" 
              className="mx-auto max-w-full h-auto rounded-lg shadow-lg"
            />
          </div>
        </div>
      </section>

      {/* Services Section */}
      <section id="services" className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-4xl font-bold mb-4" style={{ color: '#007030' }}>Our Services</h2>
            <p className="text-xl text-gray-600">Professional automotive services with a personal touch. We treat every vehicle like our own.</p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            <Card className="border-2 hover:shadow-lg transition-shadow">
              <CardHeader>
                <CardTitle style={{ color: '#007030' }}>Tire Sales & Installation</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 mb-4">Premium tire brands including Michelin, Goodyear, Bridgestone, and more. Professional mounting, balancing, and alignment services.</p>
                <ul className="space-y-2">
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>New & Used Tires</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Tire Repair</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Wheel Alignment</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Tire Rotation</span>
                  </li>
                </ul>
              </CardContent>
            </Card>

            <Card className="border-2 hover:shadow-lg transition-shadow">
              <CardHeader>
                <CardTitle style={{ color: '#007030' }}>Brake Services</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 mb-4">Complete brake system services including inspection, pad replacement, rotor resurfacing, and brake fluid changes.</p>
                <ul className="space-y-2">
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Brake Inspection</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Pad Replacement</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Rotor Service</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Brake Fluid</span>
                  </li>
                </ul>
              </CardContent>
            </Card>

            <Card className="border-2 hover:shadow-lg transition-shadow">
              <CardHeader>
                <CardTitle style={{ color: '#007030' }}>Oil Changes & Maintenance</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600 mb-4">Regular maintenance services to keep your vehicle running at peak performance with quality oils and filters.</p>
                <ul className="space-y-2">
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Oil Changes</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Filter Replacement</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Fluid Checks</span>
                  </li>
                  <li className="flex items-center gap-2">
                    <Check className="h-4 w-4" style={{ color: '#007030' }} />
                    <span>Multi-Point Inspection</span>
                  </li>
                </ul>
              </CardContent>
            </Card>
          </div>

          <div className="text-center mt-12">
            <div className="bg-white p-8 rounded-lg shadow-lg inline-block">
              <h3 className="text-2xl font-bold mb-4" style={{ color: '#007030' }}>Need Service Today?</h3>
              <p className="text-gray-600 mb-6">Call us now or stop by our shop. Most services available same day!</p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Button className="text-white" style={{ backgroundColor: '#007030' }}>
                  <Phone className="h-4 w-4 mr-2" />
                  Call (503) 367-9714
                </Button>
                <Button variant="outline" style={{ borderColor: '#007030', color: '#007030' }}>
                  <MapPin className="h-4 w-4 mr-2" />
                  Get Directions
                </Button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* About Section */}
      <section id="about" className="py-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-4xl font-bold mb-4" style={{ color: '#007030' }}>About Oregon Tires</h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              Your trusted automotive service center in Portland, OR. We provide comprehensive tire services and automotive repairs with a commitment to excellence and customer satisfaction.
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div className="text-center">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Check className="h-8 w-8" style={{ color: '#007030' }} />
              </div>
              <h3 className="text-xl font-bold mb-2" style={{ color: '#007030' }}>Expert Team</h3>
              <p className="text-gray-600">Over 15 years of automotive experience with certified technicians</p>
            </div>

            <div className="text-center">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Star className="h-8 w-8" style={{ color: '#007030' }} />
              </div>
              <h3 className="text-xl font-bold mb-2" style={{ color: '#007030' }}>Quality Service</h3>
              <p className="text-gray-600">We use only premium parts and provide warranty on all our work</p>
            </div>

            <div className="text-center">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Clock className="h-8 w-8" style={{ color: '#007030' }} />
              </div>
              <h3 className="text-xl font-bold mb-2" style={{ color: '#007030' }}>Fast Service</h3>
              <p className="text-gray-600">Most services completed same day with competitive pricing</p>
            </div>

            <div className="text-center">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Check className="h-8 w-8" style={{ color: '#007030' }} />
              </div>
              <h3 className="text-xl font-bold mb-2" style={{ color: '#007030' }}>Full Service</h3>
              <p className="text-gray-600">From tire installation to complete automotive repair solutions</p>
            </div>
          </div>

          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <h3 className="text-3xl font-bold mb-6" style={{ color: '#007030' }}>Our Story</h3>
              <p className="text-gray-600 mb-4">
                Oregon Tires has been serving the Portland community since 2008. What started as a small tire shop has grown into a full-service automotive center, but we've never forgotten our roots or our commitment to treating every customer like family.
              </p>
              <p className="text-gray-600 mb-6">
                We believe in honest, transparent service. Our certified technicians will always explain what your vehicle needs and why, giving you the information you need to make the best decision for your safety and budget.
              </p>

              <div className="grid grid-cols-2 gap-8">
                <div className="text-center">
                  <div className="text-4xl font-bold mb-2" style={{ color: '#007030' }}>15+</div>
                  <div className="text-gray-600">Years Experience</div>
                </div>
                <div className="text-center">
                  <div className="text-4xl font-bold mb-2" style={{ color: '#007030' }}>5000+</div>
                  <div className="text-gray-600">Happy Customers</div>
                </div>
              </div>
            </div>

            <div>
              <h3 className="text-3xl font-bold mb-6" style={{ color: '#007030' }}>Why Choose Us?</h3>
              <ul className="space-y-4">
                <li className="flex items-center gap-3">
                  <Check className="h-5 w-5" style={{ color: '#007030' }} />
                  <span className="text-gray-700">ASE Certified Technicians</span>
                </li>
                <li className="flex items-center gap-3">
                  <Check className="h-5 w-5" style={{ color: '#007030' }} />
                  <span className="text-gray-700">Same Day Service Available</span>
                </li>
                <li className="flex items-center gap-3">
                  <Check className="h-5 w-5" style={{ color: '#007030' }} />
                  <span className="text-gray-700">Family Owned & Operated</span>
                </li>
                <li className="flex items-center gap-3">
                  <Check className="h-5 w-5" style={{ color: '#007030' }} />
                  <span className="text-gray-700">Comprehensive High Quality Auto Care</span>
                </li>
              </ul>

              <div className="mt-8 p-6 bg-green-50 rounded-lg">
                <h4 className="text-xl font-bold mb-2" style={{ color: '#007030' }}>Our Promise</h4>
                <p className="text-gray-600">
                  We guarantee quality workmanship and stand behind every service with our comprehensive warranty. Your satisfaction is our top priority.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Testimonials Section */}
      <section className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-4xl font-bold mb-4" style={{ color: '#007030' }}>What Our Customers Say</h2>
            <p className="text-xl text-gray-600">Don't just take our word for it. Here's what real customers are saying about their experience with Oregon Tires.</p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <Card className="bg-white">
              <CardHeader>
                <div className="flex items-center gap-2 mb-2">
                  <div className="flex">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                    ))}
                  </div>
                  <Badge variant="secondary">Verified</Badge>
                </div>
                <CardTitle className="text-lg">Sarah Johnson</CardTitle>
                <p className="text-sm text-gray-500">2 weeks ago</p>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600">
                  Excellent service! They were able to fit me in same day for a tire repair. The staff was friendly and professional, and the price was very reasonable. Will definitely be back!
                </p>
              </CardContent>
            </Card>

            <Card className="bg-white">
              <CardHeader>
                <div className="flex items-center gap-2 mb-2">
                  <div className="flex">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                    ))}
                  </div>
                  <Badge variant="secondary">Verified</Badge>
                </div>
                <CardTitle className="text-lg">Mike Rodriguez</CardTitle>
                <p className="text-sm text-gray-500">1 month ago</p>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600">
                  Oregon Tires has been my go-to shop for years. They always provide honest assessments and quality work. Recently had all four tires replaced and couldn't be happier with the service.
                </p>
              </CardContent>
            </Card>

            <Card className="bg-white">
              <CardHeader>
                <div className="flex items-center gap-2 mb-2">
                  <div className="flex">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                    ))}
                  </div>
                  <Badge variant="secondary">Verified</Badge>
                </div>
                <CardTitle className="text-lg">Jennifer Chen</CardTitle>
                <p className="text-sm text-gray-500">3 weeks ago</p>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600">
                  Great experience from start to finish. They explained everything clearly, completed the work quickly, and the pricing was fair. Highly recommend for anyone needing tire service in Portland!
                </p>
              </CardContent>
            </Card>

            <Card className="bg-white">
              <CardHeader>
                <div className="flex items-center gap-2 mb-2">
                  <div className="flex">
                    {[...Array(4)].map((_, i) => (
                      <Star key={i} className="h-4 w-4 fill-current text-yellow-400" />
                    ))}
                    <Star className="h-4 w-4 text-gray-300" />
                  </div>
                  <Badge variant="secondary">Verified</Badge>
                </div>
                <CardTitle className="text-lg">David Thompson</CardTitle>
                <p className="text-sm text-gray-500">1 week ago</p>
              </CardHeader>
              <CardContent>
                <p className="text-gray-600">
                  Fast and reliable service. Had a flat tire and they patched it up perfectly. The waiting area was clean and comfortable. Only minor complaint was the wait time, but understandable given how busy they are.
                </p>
              </CardContent>
            </Card>
          </div>

          <div className="text-center">
            <div className="inline-block bg-white p-6 rounded-lg shadow-lg">
              <div className="text-4xl font-bold mb-2" style={{ color: '#007030' }}>4.8</div>
              <div className="text-gray-600 mb-2">out of 5</div>
              <div className="flex justify-center mb-2">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="h-5 w-5 fill-current text-yellow-400" />
                ))}
              </div>
              <div className="text-gray-600 mb-4">Based on 150+ Google Reviews</div>
              <Button variant="outline" style={{ borderColor: '#007030', color: '#007030' }}>
                View All Reviews on Google
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Contact Section */}
      <section id="contact" className="py-16">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <h2 className="text-4xl font-bold mb-4" style={{ color: '#007030' }}>Contact Us</h2>
            <p className="text-xl text-gray-600">Ready to schedule your service? Get in touch with us today for a free consultation.</p>
          </div>

          <div className="grid lg:grid-cols-2 gap-12">
            {/* Contact Information */}
            <div>
              <h3 className="text-2xl font-bold mb-6" style={{ color: '#007030' }}>Contact Information</h3>
              
              <div className="space-y-6">
                <div className="flex items-center gap-4">
                  <div className="bg-green-100 p-3 rounded-full">
                    <Phone className="h-6 w-6" style={{ color: '#007030' }} />
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-800">Phone</h4>
                    <p className="text-gray-600">(503) 367-9714</p>
                  </div>
                </div>

                <div className="flex items-center gap-4">
                  <div className="bg-green-100 p-3 rounded-full">
                    <MapPin className="h-6 w-6" style={{ color: '#007030' }} />
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-800">Address</h4>
                    <p className="text-gray-600">Portland, OR</p>
                  </div>
                </div>

                <div className="flex items-center gap-4">
                  <div className="bg-green-100 p-3 rounded-full">
                    <Clock className="h-6 w-6" style={{ color: '#007030' }} />
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-800">Hours</h4>
                    <p className="text-gray-600">Mon-Sat: 7AM-7PM</p>
                  </div>
                </div>

                <div className="flex items-center gap-4">
                  <div className="bg-green-100 p-3 rounded-full">
                    <MessageCircle className="h-6 w-6" style={{ color: '#007030' }} />
                  </div>
                  <div>
                    <h4 className="font-semibold text-gray-800">Language / Idioma</h4>
                    <p className="text-gray-600">🇺🇸 English</p>
                  </div>
                </div>
              </div>

              <div className="mt-8 p-6 bg-green-50 rounded-lg">
                <h4 className="text-xl font-bold mb-2" style={{ color: '#007030' }}>Visit Our Location</h4>
                <p className="text-gray-600">Portland, OR</p>
              </div>
            </div>

            {/* Contact Form */}
            <div>
              <Card>
                <CardHeader>
                  <CardTitle style={{ color: '#007030' }}>Send Message</CardTitle>
                </CardHeader>
                <CardContent>
                  <form onSubmit={handleContactSubmit} className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          First Name *
                        </label>
                        <input
                          type="text"
                          required
                          placeholder="Your first name"
                          className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          value={contactForm.firstName}
                          onChange={(e) => setContactForm({...contactForm, firstName: e.target.value})}
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                          Last Name *
                        </label>
                        <input
                          type="text"
                          required
                          placeholder="Your last name"
                          className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          value={contactForm.lastName}
                          onChange={(e) => setContactForm({...contactForm, lastName: e.target.value})}
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Phone *
                      </label>
                      <input
                        type="tel"
                        required
                        placeholder="(503) 123-4567"
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={contactForm.phone}
                        onChange={(e) => setContactForm({...contactForm, phone: e.target.value})}
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Email *
                      </label>
                      <input
                        type="email"
                        required
                        placeholder="tu@email.com"
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={contactForm.email}
                        onChange={(e) => setContactForm({...contactForm, email: e.target.value})}
                      />
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Message *
                      </label>
                      <textarea
                        required
                        rows={4}
                        placeholder="Your message..."
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={contactForm.message}
                        onChange={(e) => setContactForm({...contactForm, message: e.target.value})}
                      />
                    </div>

                    <Button 
                      type="submit" 
                      className="w-full text-white"
                      style={{ backgroundColor: '#007030' }}
                    >
                      Send Message
                    </Button>
                  </form>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </section>

      {/* Appointment Section */}
      <section id="appointment" className="py-16 bg-gray-50">
        <div className="container mx-auto px-4">
          <div className="max-w-2xl mx-auto">
            <Card>
              <CardHeader>
                <CardTitle className="text-center text-2xl" style={{ color: '#007030' }}>
                  Schedule Your Service
                </CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleAppointmentSubmit} className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Name *
                    </label>
                    <input
                      type="text"
                      required
                      placeholder="Your full name"
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={appointmentForm.name}
                      onChange={(e) => setAppointmentForm({...appointmentForm, name: e.target.value})}
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Phone *
                      </label>
                      <input
                        type="tel"
                        required
                        placeholder="(503) 123-4567"
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={appointmentForm.phone}
                        onChange={(e) => setAppointmentForm({...appointmentForm, phone: e.target.value})}
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-1">
                        Email *
                      </label>
                      <input
                        type="email"
                        required
                        placeholder="your@email.com"
                        className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        value={appointmentForm.email}
                        onChange={(e) => setAppointmentForm({...appointmentForm, email: e.target.value})}
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Service Needed *
                    </label>
                    <select
                      required
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={appointmentForm.service}
                      onChange={(e) => setAppointmentForm({...appointmentForm, service: e.target.value})}
                    >
                      <option value="">Select a service</option>
                      <option value="Tire Installation">Tire Installation</option>
                      <option value="Tire Repair">Tire Repair</option>
                      <option value="Wheel Alignment">Wheel Alignment</option>
                      <option value="Brake Service">Brake Service</option>
                      <option value="Oil Change">Oil Change</option>
                      <option value="General Maintenance">General Maintenance</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Preferred Date
                    </label>
                    <input
                      type="date"
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={appointmentForm.preferred_date}
                      onChange={(e) => setAppointmentForm({...appointmentForm, preferred_date: e.target.value})}
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Additional Information
                    </label>
                    <textarea
                      rows={3}
                      placeholder="Tell us more about your vehicle or service needs..."
                      className="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      value={appointmentForm.message}
                      onChange={(e) => setAppointmentForm({...appointmentForm, message: e.target.value})}
                    />
                  </div>

                  <Button 
                    type="submit" 
                    className="w-full text-white"
                    style={{ backgroundColor: '#007030' }}
                  >
                    <Calendar className="h-4 w-4 mr-2" />
                    Schedule Appointment
                  </Button>
                </form>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12" style={{ backgroundColor: '#007030' }}>
        <div className="container mx-auto px-4">
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <div>
              <div className="flex items-center gap-4 mb-4">
                <img 
                  src="/lovable-uploads/f000a232-32e4-4f91-8b69-f7e61ac811f2.png" 
                  alt="Oregon Tires Logo" 
                  className="h-12 w-12"
                />
                <div>
                  <h3 className="text-white font-bold text-lg">Oregon Tires</h3>
                  <p className="text-white text-sm">High Quality Auto Care</p>
                </div>
              </div>
              <p className="text-white text-sm mb-4">
                Your trusted automotive service center in Portland, Oregon. Professional service in English and Spanish for over 15 years.
              </p>
              <div className="text-white text-sm">
                <span className="bg-yellow-400 text-black px-2 py-1 rounded text-xs font-bold">5-Star Service</span>
              </div>
            </div>

            <div>
              <h4 className="text-white font-bold mb-4">Contact Information</h4>
              <div className="space-y-2 text-white text-sm">
                <div className="flex items-center gap-2">
                  <Phone className="h-4 w-4" />
                  <div>
                    <div>(503) 367-9714</div>
                    <div className="text-xs text-gray-300">Call for service</div>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <MapPin className="h-4 w-4" />
                  <div>
                    <div>Portland, Oregon</div>
                    <div className="text-xs text-gray-300">Greater Portland Area</div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h4 className="text-white font-bold mb-4">Business Hours</h4>
              <div className="text-white text-sm space-y-1">
                <div>Monday - Saturday:</div>
                <div className="font-semibold">7AM - 7PM</div>
                <div>Sunday:</div>
                <div className="font-semibold">Closed</div>
                <div className="text-yellow-400 text-xs mt-2">Same Day Service Available</div>
              </div>
            </div>

            <div>
              <h4 className="text-white font-bold mb-4">Our Services</h4>
              <div className="text-white text-sm space-y-1">
                <div>• Tire Sales & Installation</div>
                <div>• Brake Services</div>
                <div>• Oil Changes</div>
                <div>• Vehicle Maintenance</div>
                <div>• Automotive Repairs</div>
                <div className="mt-2">🇺🇸 English & 🇲🇽 Spanish Speaking</div>
              </div>
            </div>
          </div>

          <div className="border-t border-green-600 pt-8 text-center text-white text-sm">
            <p>© 2025 Oregon Tires High Quality Auto Care. All rights reserved.</p>
            <p className="mt-2">Licensed & Insured • Serving Portland Since 2009</p>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default OregonTires;
