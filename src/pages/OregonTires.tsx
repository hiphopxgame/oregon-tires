
import React from 'react';
import OregonTiresHeader from "@/components/OregonTiresHeader";
import OregonTiresHero from "@/components/OregonTiresHero";
import OregonTiresServices from "@/components/OregonTiresServices";
import OregonTiresAbout from "@/components/OregonTiresAbout";
import OregonTiresTestimonials from "@/components/OregonTiresTestimonials";
import OregonTiresContact from "@/components/OregonTiresContact";
import OregonTiresFooter from "@/components/OregonTiresFooter";
import { OregonTiresGallery } from "@/components/OregonTiresGallery";
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

      <div id="home" className="scroll-mt-24">
        <OregonTiresHero
          translations={t}
          language={language}
          primaryColor={currentDesign.primaryColor}
          secondaryColor={currentDesign.secondaryColor}
          openContactForm={openContactForm}
          openScheduleForm={openScheduleForm}
        />
      </div>

      <OregonTiresServices
        translations={t}
        primaryColor={currentDesign.primaryColor}
        secondaryColor={currentDesign.secondaryColor}
      />

      <OregonTiresAbout
        translations={t}
        primaryColor={currentDesign.primaryColor}
        secondaryColor={currentDesign.secondaryColor}
      />

      <OregonTiresTestimonials
        translations={t}
        primaryColor={currentDesign.primaryColor}
      />

      <OregonTiresGallery
        language={language}
        translations={t}
        primaryColor={currentDesign.primaryColor}
      />

      <div id="contact" className="scroll-mt-24">
        <OregonTiresContact
          language={language}
          translations={t}
          primaryColor={currentDesign.primaryColor}
          isScheduleMode={false}
          setIsScheduleMode={setIsScheduleMode}
          contactForm={contactForm}
          setContactForm={setContactForm}
          handleContactSubmit={(e) => handleContactSubmit(e, false)}
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
