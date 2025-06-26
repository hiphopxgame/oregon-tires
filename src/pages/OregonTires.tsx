
import React from 'react';
import OregonTiresHeader from "@/components/OregonTiresHeader";
import OregonTiresHero from "@/components/OregonTiresHero";
import OregonTiresServices from "@/components/OregonTiresServices";
import OregonTiresAbout from "@/components/OregonTiresAbout";
import OregonTiresTestimonials from "@/components/OregonTiresTestimonials";
import OregonTiresContact from "@/components/OregonTiresContact";
import OregonTiresFooter from "@/components/OregonTiresFooter";
import { useLanguage } from "@/hooks/useLanguage";
import { useDesignTheme } from "@/hooks/useDesignTheme";
import { useNavigation } from "@/hooks/useNavigation";
import { useContactForm } from "@/hooks/useContactForm";

const OregonTires = () => {
  const { language, t, toggleLanguage } = useLanguage();
  const { currentDesign } = useDesignTheme();
  const { 
    scrollToSection, 
    openContactForm, 
    openScheduleForm 
  } = useNavigation();
  const { contactForm, setContactForm, handleContactSubmit } = useContactForm(language, t);

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

      <section id="home" className="pt-8">
        <OregonTiresHero
          translations={t}
          primaryColor={currentDesign.primaryColor}
          secondaryColor={currentDesign.secondaryColor}
          openContactForm={openContactForm}
          openScheduleForm={openScheduleForm}
        />
      </section>

      <section id="services" className="pt-8">
        <OregonTiresServices
          translations={t}
          primaryColor={currentDesign.primaryColor}
          secondaryColor={currentDesign.secondaryColor}
        />
      </section>

      <OregonTiresAbout
        translations={t}
        primaryColor={currentDesign.primaryColor}
        secondaryColor={currentDesign.secondaryColor}
      />

      <OregonTiresTestimonials
        translations={t}
        primaryColor={currentDesign.primaryColor}
      />

      <section id="contact" className="pt-8">
        <OregonTiresContact
          language={language}
          translations={t}
          primaryColor={currentDesign.primaryColor}
          contactForm={contactForm}
          setContactForm={setContactForm}
          handleContactSubmit={handleContactSubmit}
          toggleLanguage={toggleLanguage}
        />
      </section>

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
