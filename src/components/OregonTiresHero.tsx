
import React from 'react';
import { Button } from "@/components/ui/button";
import { Link } from "react-router-dom";

interface HeroProps {
  translations: any;
  language: string;
  primaryColor: string;
  secondaryColor: string;
  openContactForm: () => void;
  openScheduleForm: () => void;
}

const OregonTiresHero: React.FC<HeroProps> = ({
  translations,
  language,
  primaryColor,
  secondaryColor,
  openContactForm,
  openScheduleForm
}) => {
  const t = translations;

  return (
    <section 
      className="text-white py-20 relative"
      style={{ 
        backgroundColor: primaryColor,
        backgroundImage: `url('/lovable-uploads/afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png')`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat'
      }}
    >
      {/* Dark overlay for better text readability */}
      <div className="absolute inset-0 bg-black/50"></div>
      
      <div className="container mx-auto px-4 text-center relative z-10">
        <h1 className="text-5xl font-bold mb-8 drop-shadow-2xl">{t.heroTitle}</h1>
        
        <div className="flex justify-center gap-4 flex-wrap">
          <Button
            onClick={openContactForm}
            className="text-black px-8 py-3 rounded-lg font-semibold hover:bg-yellow-400 transition-colors shadow-lg"
            style={{ backgroundColor: secondaryColor }}
          >
            {t.contact}
          </Button>
          <Link to={language === 'english' ? "/book-appointment" : "/programar-servicio"}>
            <Button 
              className="bg-white text-black px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors shadow-lg"
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
