
import React from 'react';
import { Phone, MapPin, Clock } from 'lucide-react';

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
    <footer style={{ backgroundColor: primaryColor }} className="text-white py-12">
      <div className="container mx-auto px-4">
        <div className="grid md:grid-cols-3 gap-8">
          <div>
            <h3 className="text-xl font-bold mb-4">{t.contactInfo}</h3>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <Phone className="h-4 w-4" />
                <span>(503) 367-9714</span>
              </div>
              <div className="flex items-center gap-2">
                <MapPin className="h-4 w-4" />
                <span>8536 SE 82nd Ave, Portland, OR 97266</span>
              </div>
              <div className="flex items-center gap-2">
                <Clock className="h-4 w-4" />
                <span>{t.monSat}</span>
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
            <h3 className="text-xl font-bold mb-4">Language / Idioma</h3>
            <button 
              onClick={toggleLanguage} 
              className="text-white hover:text-yellow-200 text-left mb-4"
            >
              English | Español
            </button>
          </div>
        </div>

        <div className="border-t border-green-600 mt-8 pt-8 text-center text-gray-200">
          <p>&copy; 2025 Oregon Tires Auto Care. {t.allRightsReserved}</p>
        </div>
      </div>
    </footer>
  );
};

export default OregonTiresFooter;
