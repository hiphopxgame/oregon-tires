import React, { useState, useEffect } from 'react';
import OregonTiresHeader from "@/components/OregonTiresHeader";
import OregonTiresHero from "@/components/OregonTiresHero";
import OregonTiresServices from "@/components/OregonTiresServices";
import OregonTiresAbout from "@/components/OregonTiresAbout";
import OregonTiresTestimonials from "@/components/OregonTiresTestimonials";
import OregonTiresContact from "@/components/OregonTiresContact";
import OregonTiresFooter from "@/components/OregonTiresFooter";
import translations from "@/utils/translations";
import { supabase } from "@/integrations/supabase/client";
import { toast } from "@/hooks/use-toast";

const OregonTires = () => {
  const [language, setLanguage] = useState('english');
  const [t, setT] = useState(translations['english']);
  const [isScheduleMode, setIsScheduleMode] = useState(false);
  const [contactForm, setContactForm] = useState({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    message: '',
    service: '',
    preferred_date: '',
    preferred_time: ''
  });
  const [currentDesign, setCurrentDesign] = useState({
    primaryColor: '#007030',
    secondaryColor: '#FEE11A',
    backgroundColor: '#f0fdf4',
    textColor: '#000000',
  });

  const toggleLanguage = () => {
    const newLanguage = language === 'english' ? 'spanish' : 'english';
    setLanguage(newLanguage);
    setT(translations[newLanguage]);
  };

  const openContactForm = () => {
    setIsScheduleMode(false);
    scrollToSection('contact');
  };

  const openScheduleForm = () => {
    setIsScheduleMode(true);
    scrollToSection('contact');
  };

  useEffect(() => {
    // Load the design from localStorage on component mount
    const storedDesign = localStorage.getItem('currentDesign');
    if (storedDesign) {
      setCurrentDesign(JSON.parse(storedDesign));
    }
  }, []);

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
      if (isScheduleMode) {
        // Add schedule-specific fields
        const appointmentData = {
          ...formData,
          service: contactForm.service,
          preferred_date: contactForm.preferred_date,
          preferred_time: contactForm.preferred_time
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
        // Contact message
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
        preferred_time: ''
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

  const scrollToSection = (id: string) => {
    const element = document.getElementById(id);
    if (element) {
      const headerHeight = 120; // Account for sticky header
      const elementPosition = element.offsetTop - headerHeight;
      window.scrollTo({ 
        top: elementPosition, 
        behavior: 'smooth' 
      });
    }
  };

  return (
    <div className="min-h-screen" style={{ backgroundColor: currentDesign.backgroundColor, color: currentDesign.textColor }}>
      <OregonTiresHeader
        language={language}
        translations={t}
        primaryColor={currentDesign.primaryColor}
        toggleLanguage={toggleLanguage}
        scrollToSection={scrollToSection}
        openScheduleForm={openScheduleForm}
        openContactForm={openContactForm}
      />

      <div id="home">
        <OregonTiresHero
          translations={t}
          primaryColor={currentDesign.primaryColor}
          secondaryColor={currentDesign.secondaryColor}
          openContactForm={openContactForm}
          openScheduleForm={openScheduleForm}
        />
      </div>

      <div id="services">
        <OregonTiresServices
          translations={t}
          primaryColor={currentDesign.primaryColor}
          secondaryColor={currentDesign.secondaryColor}
        />
      </div>

      <div id="about">
        <OregonTiresAbout
          translations={t}
          primaryColor={currentDesign.primaryColor}
        />
      </div>

      <OregonTiresTestimonials
        translations={t}
        primaryColor={currentDesign.primaryColor}
      />

      <div id="contact">
        <OregonTiresContact
          language={language}
          translations={t}
          primaryColor={currentDesign.primaryColor}
          isScheduleMode={isScheduleMode}
          setIsScheduleMode={setIsScheduleMode}
          contactForm={contactForm}
          setContactForm={setContactForm}
          handleContactSubmit={handleContactSubmit}
          toggleLanguage={toggleLanguage}
        />
      </div>

      <OregonTiresFooter
        language={language}
        translations={t}
        primaryColor={currentDesign.primaryColor}
        openContactForm={openContactForm}
        toggleLanguage={toggleLanguage}
      />
    </div>
  );
};

export default OregonTires;
