
import React, { useState, useEffect } from 'react';
import { supabase } from "@/integrations/supabase/client";
import { toast } from "sonner";
import OregonTiresHeader from "@/components/OregonTiresHeader";
import OregonTiresHero from "@/components/OregonTiresHero";
import OregonTiresServices from "@/components/OregonTiresServices";
import OregonTiresAbout from "@/components/OregonTiresAbout";
import OregonTiresTestimonials from "@/components/OregonTiresTestimonials";
import OregonTiresContact from "@/components/OregonTiresContact";
import OregonTiresFooter from "@/components/OregonTiresFooter";

const OregonTires = () => {
  const [language, setLanguage] = useState('english');
  const [isScheduleMode, setIsScheduleMode] = useState(false);
  const [currentDesign, setCurrentDesign] = useState('design1');
  
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

  // Load current design preference
  useEffect(() => {
    const loadDesignSetting = async () => {
      try {
        const { data, error } = await supabase
          .from('oretir_settings')
          .select('setting_value')
          .eq('setting_key', 'website_design')
          .maybeSingle();

        if (!error && data) {
          setCurrentDesign(data.setting_value);
        }
      } catch (error) {
        console.error('Error loading design setting:', error);
      }
    };

    loadDesignSetting();
  }, []);

  const translations = {
    english: {
      title: "Oregon Tires",
      subtitle: "High Quality Auto Care",
      heroTitle: "High Quality Auto Services & Expert Care",
      heroSubtitle: "Your trusted automotive service center in Portland, Oregon. Quality tires, expert service, unbeatable prices.",
      contactButton: "Contact Oregon Tires",
      appointmentButton: "Book an Appointment",
      services: "Our Services",
      servicesSubtitle: "Professional automotive services with a personal touch. We treat every vehicle like our own.",
      about: "About Oregon Tires",
      aboutSubtitle: "Your trusted automotive service center in Portland, OR. We provide comprehensive tire services and automotive repairs with a commitment to excellence and customer satisfaction.",
      contact: "Contact Us",
      scheduleService: "Schedule Service",
      contactSubtitle: "Ready to schedule your service? Get in touch with us today for a free consultation.",
      needServiceTitle: "Need Service Today?",
      needServiceSubtitle: "Call us now or stop by our shop. Most services available same day!",
      scheduleServiceButton: "Schedule Service",
      callButton: "Call (503) 367-9714",
      contactInfo: "Contact Information",
      businessHours: "Business Hours",
      ourServices: "Our Services",
      firstName: "First Name",
      lastName: "Last Name",
      phone: "Phone",
      email: "Email",
      message: "Message",
      sendMessage: "Send Message",
      scheduleAppointment: "Schedule Appointment",
      serviceNeeded: "Service Needed",
      preferredDate: "Preferred Date",
      preferredTime: "Preferred Time",
      selectService: "Select a service",
      selectTime: "Select time",
      visitLocation: "Visit Our Location",
      getDirections: "Get Directions",
      hideMap: "Hide Map",
      toggleToSchedule: "Switch to Schedule Service",
      toggleToContact: "Switch to Contact",
      monSat: "Mon-Sat: 7AM-7PM",
      sunday: "Sunday: Closed",
      sameDayService: "Same Day Service Available"
    },
    spanish: {
      title: "Oregon Tires",
      subtitle: "Cuidado Automotriz de Alta Calidad",
      heroTitle: "Servicios Automotrices de Alta Calidad y Cuidado Experto",
      heroSubtitle: "Su centro de servicio automotriz de confianza en Portland, Oregon. Llantas de calidad, servicio experto, precios inmejorables.",
      contactButton: "Contactar Oregon Tires",
      appointmentButton: "Reservar Cita",
      services: "Nuestros Servicios",
      servicesSubtitle: "Servicios automotrices profesionales con un toque personal. Tratamos cada vehículo como si fuera nuestro.",
      about: "Acerca de Oregon Tires",
      aboutSubtitle: "Su centro de servicio automotriz de confianza en Portland, OR. Proporcionamos servicios integrales de llantas y reparaciones automotrices con un compromiso con la excelencia y satisfacción del cliente.",
      contact: "Contáctanos",
      scheduleService: "Programar Servicio",
      contactSubtitle: "¿Listo para programar su servicio? Póngase en contacto con nosotros hoy para una consulta gratuita.",
      needServiceTitle: "¿Necesita Servicio Hoy?",
      needServiceSubtitle: "¡Llámenos ahora o visite nuestro taller. La mayoría de servicios disponibles el mismo día!",
      scheduleServiceButton: "Programar Servicio",
      callButton: "Llamar (503) 367-9714",
      contactInfo: "Información de Contacto",
      businessHours: "Horario de Atención",
      ourServices: "Nuestros Servicios",
      firstName: "Nombre",
      lastName: "Apellido",
      phone: "Teléfono",
      email: "Correo Electrónico",
      message: "Mensaje",
      sendMessage: "Enviar Mensaje",
      scheduleAppointment: "Programar Cita",
      serviceNeeded: "Servicio Necesario",
      preferredDate: "Fecha Preferida",
      preferredTime: "Hora Preferida",
      selectService: "Seleccionar un servicio",
      selectTime: "Seleccionar hora",
      visitLocation: "Visite Nuestra Ubicación",
      getDirections: "Obtener Direcciones",
      hideMap: "Ocultar Mapa",
      toggleToSchedule: "Cambiar a Programar Servicio",
      toggleToContact: "Cambiar a Contacto",
      monSat: "Lun-Sáb: 7AM-7PM",
      sunday: "Domingo: Cerrado",
      sameDayService: "Servicio el Mismo Día Disponible"
    }
  };

  const t = translations[language as keyof typeof translations];

  const toggleLanguage = () => {
    setLanguage(prev => prev === 'english' ? 'spanish' : 'english');
  };

  const handleContactSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (isScheduleMode) {
        // Submit as appointment
        const { error } = await supabase
          .from('oregon_tires_appointments')
          .insert([{
            first_name: contactForm.firstName,
            last_name: contactForm.lastName,
            phone: contactForm.phone,
            email: contactForm.email,
            service: contactForm.service,
            preferred_date: contactForm.preferred_date,
            preferred_time: contactForm.preferred_time,
            message: contactForm.message,
            status: 'new',
            language: language
          }]);

        if (error) throw error;
        toast.success(language === 'english' ? "Appointment request submitted! We'll contact you to confirm." : "¡Solicitud de cita enviada! Te contactaremos para confirmar.");
      } else {
        // Submit as contact message
        const { error } = await supabase
          .from('oregon_tires_contact_messages')
          .insert([{
            first_name: contactForm.firstName,
            last_name: contactForm.lastName,
            phone: contactForm.phone,
            email: contactForm.email,
            message: contactForm.message,
            language: language
          }]);

        if (error) throw error;
        toast.success(language === 'english' ? "Message sent successfully! We'll get back to you soon." : "¡Mensaje enviado exitosamente! Te responderemos pronto.");
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
      console.error('Error submitting form:', error);
      toast.error(language === 'english' ? "Failed to submit. Please try again." : "Error al enviar. Por favor intenta de nuevo.");
    }
  };

  const scrollToSection = (id: string) => {
    const element = document.getElementById(id);
    if (element) {
      const headerHeight = 120;
      const elementPosition = element.offsetTop - headerHeight;
      window.scrollTo({
        top: elementPosition,
        behavior: 'smooth'
      });
    }
  };

  const openContactForm = () => {
    setIsScheduleMode(false);
    scrollToSection('contact');
  };

  const openScheduleForm = () => {
    setIsScheduleMode(true);
    scrollToSection('contact');
  };

  // Design variations
  const isDesign2 = currentDesign === 'design2';
  const primaryColor = isDesign2 ? '#1E40AF' : '#007030'; // Blue vs Green
  const secondaryColor = isDesign2 ? '#FBBF24' : '#FEE11A'; // Orange vs Yellow

  return (
    <div className="min-h-screen bg-white">
      <OregonTiresHeader
        language={language}
        translations={t}
        primaryColor={primaryColor}
        toggleLanguage={toggleLanguage}
        scrollToSection={scrollToSection}
        openScheduleForm={openScheduleForm}
      />

      <OregonTiresHero
        translations={t}
        primaryColor={primaryColor}
        secondaryColor={secondaryColor}
        openContactForm={openContactForm}
        openScheduleForm={openScheduleForm}
      />

      <OregonTiresServices
        translations={t}
        primaryColor={primaryColor}
        openScheduleForm={openScheduleForm}
      />

      <OregonTiresAbout
        translations={t}
        primaryColor={primaryColor}
      />

      <OregonTiresTestimonials
        primaryColor={primaryColor}
      />

      <OregonTiresContact
        language={language}
        translations={t}
        primaryColor={primaryColor}
        isScheduleMode={isScheduleMode}
        setIsScheduleMode={setIsScheduleMode}
        contactForm={contactForm}
        setContactForm={setContactForm}
        handleContactSubmit={handleContactSubmit}
        toggleLanguage={toggleLanguage}
      />

      <OregonTiresFooter
        primaryColor={primaryColor}
      />
    </div>
  );
};

export default OregonTires;
