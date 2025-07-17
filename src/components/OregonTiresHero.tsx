
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
      <div className="container mx-auto px-4 text-center relative z-10">
        <h1 className="text-5xl font-bold mb-6" style={{ textShadow: '2px 2px 0px #000, -2px -2px 0px #000, 2px -2px 0px #000, -2px 2px 0px #000' }}>{t.heroTitle}</h1>
        
        {/* Updated tagline section */}
        <div className="mb-8">
          <p className="text-xl max-w-3xl mx-auto" style={{ textShadow: '1px 1px 0px #000, -1px -1px 0px #000, 1px -1px 0px #000, -1px 1px 0px #000' }}>{t.heroSubtitle}</p>
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
