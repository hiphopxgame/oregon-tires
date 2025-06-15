
import React from 'react';
import { Phone, MapPin, Clock } from 'lucide-react';

interface ContactInformationProps {
  translations: any;
  primaryColor: string;
}

const ContactInformation: React.FC<ContactInformationProps> = ({
  translations,
  primaryColor
}) => {
  const t = translations;

  return (
    <div>
      <h3 className="text-2xl font-bold mb-6" style={{ color: primaryColor }}>{t.contactInfo}</h3>
      
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <div className="bg-green-100 p-3 rounded-full">
            <Phone className="h-6 w-6" style={{ color: primaryColor }} />
          </div>
          <div>
            <h4 className="font-semibold text-gray-800">{t.phone}</h4>
            <p className="text-gray-600">(503) 367-9714</p>
          </div>
        </div>

        <div className="flex items-center gap-4">
          <div className="bg-green-100 p-3 rounded-full">
            <MapPin className="h-6 w-6" style={{ color: primaryColor }} />
          </div>
          <div>
            <h4 className="font-semibold text-gray-800">Address</h4>
            <p className="text-gray-600">8536 SE 82nd Ave, Portland, OR 97266</p>
          </div>
        </div>

        <div className="flex items-center gap-4">
          <div className="bg-green-100 p-3 rounded-full">
            <Clock className="h-6 w-6" style={{ color: primaryColor }} />
          </div>
          <div>
            <h4 className="font-semibold text-gray-800">{t.businessHours}</h4>
            <p className="text-gray-600">{t.monSat}</p>
            <p className="text-gray-600">{t.sunday}</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ContactInformation;
