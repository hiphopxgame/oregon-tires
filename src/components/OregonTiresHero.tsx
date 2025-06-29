
import React from 'react';
import { Button } from "@/components/ui/button";
import { Link } from "react-router-dom";

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
    <section className="text-white py-20" style={{ backgroundColor: primaryColor }}>
      <div className="container mx-auto px-4 text-center">
        {/* Logo Image */}
        <div className="mb-8">
          <img 
            src="/lovable-uploads/b5b7fac4-56f8-4e79-bb2e-57fe29b15867.png" 
            alt="Oregon Tires Auto Care - Spanish & English Speaking" 
            className="mx-auto max-w-full h-auto"
            style={{ maxHeight: '300px' }}
          />
        </div>
        
        <h1 className="text-5xl font-bold mb-6">{t.heroTitle}</h1>
        
        {/* Updated tagline section with logo */}
        <div className="flex items-center justify-center gap-6 mb-8">
          <img 
            src="/lovable-uploads/95d0baa7-ee82-44bc-817a-34d47eb2e553.png" 
            alt="Oregon Tires Logo" 
            className="h-16 w-16"
          />
          <p className="text-xl max-w-3xl">{t.heroSubtitle}</p>
        </div>
        
        <div className="flex justify-center gap-4 flex-wrap">
          <Button
            onClick={openContactForm}
            className="text-black px-8 py-3 rounded-lg font-semibold hover:bg-yellow-400 transition-colors"
            style={{ backgroundColor: secondaryColor }}
          >
            {t.contact}
          </Button>
          <Link to="/book-appointment">
            <Button 
              className="bg-white text-black px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
              style={{ color: primaryColor }}
            >
              {t.scheduleService}
            </Button>
          </Link>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresHero;
