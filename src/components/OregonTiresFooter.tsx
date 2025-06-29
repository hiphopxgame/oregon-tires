
import React from 'react';
import { Phone, MapPin, Clock, Facebook, Instagram, Mail, ExternalLink } from 'lucide-react';
import { Link } from 'react-router-dom';

interface FooterProps {
  language: string;
  translations: any;
  primaryColor: string;
  openContactForm: () => void;
  toggleLanguage: () => void;
}

const OregonTiresFooter: React.FC<FooterProps> = ({
  language,
  translations,
  primaryColor,
  openContactForm,
  toggleLanguage
}) => {
  const t = translations;

  return (
    <footer className="text-white py-12" style={{ backgroundColor: primaryColor }}>
      <div className="container mx-auto px-4">
        <div className="grid md:grid-cols-4 gap-8">
          <div>
            <h3 className="text-xl font-bold mb-4">{t.contactInfo}</h3>
            <div className="space-y-2">
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
                <span>8536 SE 82nd Ave, Portland, OR 97266</span>
              </div>
              <div className="flex items-center gap-2">
                <Clock className="h-4 w-4" />
                <span>{t.hours}</span>
              </div>
            </div>
          </div>

          <div>
            <h3 className="text-xl font-bold mb-4">{t.services}</h3>
            <ul className="space-y-1 text-gray-200">
              <li>{t.tireInstallation}</li>
              <li>{t.tireRepair}</li>
              <li>{t.wheelAlignment}</li>
              <li>{t.brakeService}</li>
              <li>{t.oilChange}</li>
            </ul>
          </div>

          <div>
            <h3 className="text-xl font-bold mb-4">Follow Us</h3>
            <div className="space-y-3">
              <a 
                href="https://www.facebook.com/61571913202998/" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-white hover:text-yellow-200 flex items-center gap-2"
              >
                <Facebook className="h-4 w-4" />
                Facebook
              </a>
              <a 
                href="https://www.instagram.com/oregontires" 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-white hover:text-yellow-200 flex items-center gap-2"
              >
                <Instagram className="h-4 w-4" />
                Instagram
              </a>
            </div>
            <div className="mt-4">
              <h4 className="text-lg font-bold mb-2">Language / Idioma</h4>
              <button 
                onClick={toggleLanguage} 
                className="text-white hover:text-yellow-200 text-left"
              >
                English | Español
              </button>
            </div>
          </div>

          <div>
            <h3 className="text-xl font-bold mb-4">Other Versions</h3>
            <div className="space-y-2">
              <a 
                href="/oregon-tires.html" 
                className="text-white hover:text-yellow-200 flex items-center gap-2 block"
              >
                <ExternalLink className="h-4 w-4" />
                Static Version
              </a>
              <Link 
                to="/admin" 
                className="text-white hover:text-yellow-200 flex items-center gap-2 block"
              >
                <ExternalLink className="h-4 w-4" />
                Admin Dashboard
              </Link>
            </div>
          </div>
        </div>

        <div className="border-t border-green-600 mt-8 pt-8 text-center text-gray-200">
          <p>&copy; 2025 Oregon Tires Auto Care. {t.allRights}</p>
        </div>
      </div>
    </footer>
  );
};

export default OregonTiresFooter;
