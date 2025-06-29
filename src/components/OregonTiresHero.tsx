
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
        {/* Logo Image - Full Width */}
        <div className="mb-8 -mx-4">
          <img 
            src="/lovable-uploads/b5b7fac4-56f8-4e79-bb2e-57fe29b15867.png" 
            alt="Oregon Tires Auto Care - Spanish & English Speaking" 
            className="w-full h-auto"
          />
        </div>
        
        <h1 className="text-5xl font-bold mb-6">{t.heroTitle}</h1>
        
        {/* Updated tagline section */}
        <div className="mb-8">
          <p className="text-xl max-w-3xl mx-auto">{t.heroSubtitle}</p>
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
