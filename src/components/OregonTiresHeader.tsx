
import React from 'react';
import { Phone, MapPin, Clock } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Link } from "react-router-dom";

interface HeaderProps {
  language: string;
  translations: any;
  primaryColor: string;
  toggleLanguage: () => void;
  scrollToSection: (id: string) => void;
  openScheduleForm: () => void;
}

const OregonTiresHeader: React.FC<HeaderProps> = ({
  language,
  translations,
  primaryColor,
  toggleLanguage,
  scrollToSection,
  openScheduleForm
}) => {
  const t = translations;

  return (
    <header className="bg-white shadow-sm sticky top-0 z-50">
      <div className="container mx-auto px-4 py-3">
        {/* Top Bar */}
        <div style={{ backgroundColor: primaryColor }} className="text-white py-2 px-4 rounded-md mb-4">
          <div className="flex flex-col lg:flex-row justify-between items-center text-sm">
            <div className="flex flex-col sm:flex-row items-center gap-4 mb-2 lg:mb-0">
              <div className="flex items-center gap-1">
                <Phone className="h-4 w-4" />
                (503) 367-9714
              </div>
              <div className="flex items-center gap-1">
                <MapPin className="h-4 w-4" />
                8536 SE 82nd Ave, Portland, OR 97266
              </div>
              <div className="flex items-center gap-1">
                <Clock className="h-4 w-4" />
                {t.monSat}
              </div>
            </div>
            <button onClick={toggleLanguage} className="text-white hover:text-yellow-200">
              {language === 'english' ? '🇺🇸 English' : '🇲🇽 Español'}
            </button>
          </div>
        </div>

        {/* Main Header */}
        <div className="flex flex-col lg:flex-row justify-between items-center">
          <Link to="/" className="flex items-center gap-4 mb-4 lg:mb-0">
            <img 
              src="/lovable-uploads/f000a232-32e4-4f91-8b69-f7e61ac811f2.png" 
              alt="Oregon Tires Logo" 
              className="h-16 w-16"
            />
            <div>
              <h1 className="text-2xl font-bold" style={{ color: primaryColor }}>{t.title}</h1>
              <p className="text-lg text-gray-600">{t.subtitle}</p>
            </div>
          </Link>

          {/* Navigation */}
          <nav className="flex flex-wrap items-center gap-6">
            <button onClick={() => scrollToSection('home')} className="text-gray-700 hover:text-green-700 font-medium">Home</button>
            <button onClick={() => scrollToSection('services')} className="text-gray-700 hover:text-green-700 font-medium">{t.services}</button>
            <button onClick={() => scrollToSection('about')} className="text-gray-700 hover:text-green-700 font-medium">{t.about}</button>
            <button onClick={() => scrollToSection('contact')} className="text-gray-700 hover:text-green-700 font-medium">{t.contact}</button>
            <Button 
              className="text-white font-medium"
              style={{ backgroundColor: primaryColor }}
              onClick={openScheduleForm}
            >
              {t.scheduleService}
            </Button>
          </nav>
        </div>
      </div>
    </header>
  );
};

export default OregonTiresHeader;
