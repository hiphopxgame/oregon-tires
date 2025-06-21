
import React from 'react';
import { Button } from "@/components/ui/button";

interface HeroProps {
  translations: any;
  primaryColor: string;
  secondaryColor: string;
  openContactForm: () => void;
  openScheduleForm: () => void;
}

const OregonTiresHero: React.FC<HeroProps> = ({
  translations,
  primaryColor,
  secondaryColor,
  openContactForm,
  openScheduleForm
}) => {
  const t = translations;

  return (
    <>
      {/* Hero Section */}
      <section id="home" className="py-20" style={{ backgroundColor: primaryColor }}>
        <div className="container mx-auto px-4 text-center">
          <div className="max-w-4xl mx-auto">
            <h2 className="text-5xl font-bold text-white mb-4">
              {t.heroTitle}
            </h2>
            <p className="text-xl text-white mb-8">
              {t.heroSubtitle}
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button 
                size="lg" 
                className="text-black font-bold"
                style={{ backgroundColor: secondaryColor }}
                onClick={openContactForm}
              >
                {t.contactButton}
              </Button>
              <Button 
                size="lg" 
                className="text-black border-2 hover:bg-white hover:text-green-700"
                style={{ backgroundColor: secondaryColor, borderColor: secondaryColor }}
                onClick={openScheduleForm}
              >
                {t.appointmentButton}
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
    </>
  );
};

export default OregonTiresHero;
