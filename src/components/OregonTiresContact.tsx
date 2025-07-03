
import React from 'react';
import ContactInformation from './contact/ContactInformation';
import ContactForm from './contact/ContactForm';
import LocationMap from './contact/LocationMap';
import WeeklySchedule from './WeeklySchedule';

interface ContactProps {
  language: string;
  translations: any;
  primaryColor: string;
  isScheduleMode: boolean;
  setIsScheduleMode: (value: boolean) => void;
  contactForm: any;
  setContactForm: (form: any) => void;
  handleContactSubmit: (e: React.FormEvent) => void;
  toggleLanguage: () => void;
}

const OregonTiresContact: React.FC<ContactProps> = ({
  language,
  translations,
  primaryColor,
  isScheduleMode,
  setIsScheduleMode,
  contactForm,
  setContactForm,
  handleContactSubmit,
  toggleLanguage
}) => {
  const t = translations;

  return (
    <section id="contact" className="py-16">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>
            {isScheduleMode ? t.scheduleService : t.contact}
          </h2>
          <p className="text-xl text-gray-600">{t.contactSubtitle}</p>
        </div>

        <WeeklySchedule 
          translations={translations}
          primaryColor={primaryColor}
        />

        <div className="grid lg:grid-cols-2 gap-12">
          <ContactInformation 
            translations={translations}
            primaryColor={primaryColor}
          />

          <ContactForm
            translations={translations}
            primaryColor={primaryColor}
            isScheduleMode={isScheduleMode}
            setIsScheduleMode={setIsScheduleMode}
            contactForm={contactForm}
            setContactForm={setContactForm}
            handleContactSubmit={handleContactSubmit}
          />
        </div>

        <LocationMap 
          translations={translations}
          primaryColor={primaryColor}
        />
      </div>
    </section>
  );
};

export default OregonTiresContact;
