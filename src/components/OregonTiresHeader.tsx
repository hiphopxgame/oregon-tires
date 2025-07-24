
import React from 'react';
import { Phone, MapPin, Clock, Globe, Instagram, Facebook, Mail } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Link } from 'react-router-dom';

interface HeaderProps {
  language: string;
  translations: any;
  primaryColor: string;
  toggleLanguage: () => void;
  scrollToSection: (sectionId: string) => void;
  openScheduleForm: () => void;
  openContactForm: () => void;
}

const OregonTiresHeader: React.FC<HeaderProps> = ({
  language,
  translations,
  primaryColor,
  toggleLanguage,
  scrollToSection,
  openScheduleForm,
  openContactForm
}) => {
  const t = translations;

  return (
    <header className="bg-white shadow-lg sticky top-0 z-50">
      {/* Top Bar */}
      <div className="py-2" style={{ backgroundColor: primaryColor }}>
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center text-white text-sm">
            <div className="flex items-center space-x-6">
              <div className="flex items-center gap-2">
                <Phone className="h-4 w-4" />
                <span>(503) 367-9714</span>
              </div>
              <div className="flex items-center gap-2">
                <Mail className="h-4 w-4" />
                <span>oregontirespdx@gmail.com</span>
              </div>
              <div className="flex items-center gap-2">
                <MapPin className="h-4 w-4" />
                <span className="hidden md:inline">8536 SE 82nd Ave, Portland, OR 97266</span>
              </div>
              <div className="flex items-center gap-2">
                <Clock className="h-4 w-4" />
                <span className="hidden lg:inline">{t.monSat}</span>
              </div>
            </div>
            <div className="flex items-center space-x-4">
              {/* Social Media Links */}
              <a 
                href="https://www.instagram.com/oregontires" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-white hover:text-yellow-200 transition-colors"
              >
                <Instagram className="h-4 w-4" />
              </a>
              <a 
                href="https://www.facebook.com/61571913202998/" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-white hover:text-yellow-200 transition-colors"
              >
                <Facebook className="h-4 w-4" />
              </a>
              <button 
                onClick={toggleLanguage}
                className="flex items-center gap-1 text-white hover:text-yellow-200 transition-colors"
              >
                <Globe className="h-4 w-4" />
                <span className="text-xs">{language === 'english' ? 'ES' : 'EN'}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Main Header */}
      <div className="py-4">
        <div className="container mx-auto px-4">
          <div className="flex justify-between items-center">
            <Link to="/" className="hover:opacity-80 transition-opacity">
              <img 
                src="/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png" 
                alt="Oregon Tires Auto Care - Spanish & English Speaking" 
                className="h-16 w-auto"
              />
            </Link>
            <nav className="hidden md:flex items-center space-x-6">
              <button 
                onClick={() => scrollToSection('home')}
                className="hover:opacity-80 transition-colors"
                style={{ color: primaryColor }}
              >
                {t.home}
              </button>
              <button 
                onClick={() => scrollToSection('services')}
                className="hover:opacity-80 transition-colors"
                style={{ color: primaryColor }}
              >
                {t.services}
              </button>
              <button 
                onClick={() => scrollToSection('about')}
                className="hover:opacity-80 transition-colors"
                style={{ color: primaryColor }}
              >
                {t.about}
              </button>
              <button 
                onClick={() => scrollToSection('testimonials')}
                className="hover:opacity-80 transition-colors"
                style={{ color: primaryColor }}
              >
                {t.testimonials}
              </button>
              <button 
                onClick={() => scrollToSection('contact')}
                className="hover:opacity-80 transition-colors"
                style={{ color: primaryColor }}
              >
                {t.contact}
              </button>
              <Link to={language === 'english' ? "/book-appointment" : "/programar-servicio"}>
                <Button
                  className="bg-yellow-400 text-black hover:bg-yellow-500 px-6 py-2 rounded-lg font-semibold"
                >
                  {t.scheduleService}
                </Button>
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </header>
  );
};

export default OregonTiresHeader;
