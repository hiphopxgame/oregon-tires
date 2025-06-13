import React, { useState, useEffect } from 'react';
import OregonTiresHeader from "@/components/OregonTiresHeader";
import OregonTiresHero from "@/components/OregonTiresHero";
import OregonTiresServices from "@/components/OregonTiresServices";
import OregonTiresAbout from "@/components/OregonTiresAbout";
import OregonTiresTestimonials from "@/components/OregonTiresTestimonials";
import OregonTiresContact from "@/components/OregonTiresContact";
import OregonTiresFooter from "@/components/OregonTiresFooter";
import translations from "@/utils/translations";

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
      ...contactForm,
      status: 'new',
      type: isScheduleMode ? 'appointment' : 'contact'
    };

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_SUPABASE_URL}/rest/v1/${isScheduleMode ? 'oregon_tires_appointments' : 'oregon_tires_contact_messages'}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'apikey': process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY || ''
        },
        body: JSON.stringify(formData)
      });

      if (res.ok) {
        alert(t.formSuccess);
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
      } else {
        alert(t.formError);
      }
    } catch (error) {
      console.error("Form submission error:", error);
      alert(t.formError);
    }
  };

  const scrollToSection = (id: string) => {
    const element = document.getElementById(id);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
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
      />

      <OregonTiresHero
        translations={t}
        primaryColor={currentDesign.primaryColor}
        secondaryColor={currentDesign.secondaryColor}
        openContactForm={openContactForm}
        openScheduleForm={openScheduleForm}
      />

      <OregonTiresServices
        translations={t}
        primaryColor={currentDesign.primaryColor}
        secondaryColor={currentDesign.secondaryColor}
        openContactForm={openContactForm}
      />

      <OregonTiresAbout
        translations={t}
        primaryColor={currentDesign.primaryColor}
      />

      <OregonTiresTestimonials
        translations={t}
        primaryColor={currentDesign.primaryColor}
      />

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
