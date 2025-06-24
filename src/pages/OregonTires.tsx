
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
    isScheduleMode, 
    setIsScheduleMode, 
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
          secondaryColor={currentDesign.secondaryColor}
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
          handleContactSubmit={(e) => handleContactSubmit(e, isScheduleMode)}
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
