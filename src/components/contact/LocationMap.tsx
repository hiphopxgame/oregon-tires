
import React from 'react';

interface LocationMapProps {
  translations: any;
  primaryColor: string;
}

const LocationMap: React.FC<LocationMapProps> = ({
  translations,
  primaryColor
}) => {
  const t = translations;

  return (
    <div className="mt-12">
      <div className="text-center mb-6">
        <h3 className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>{t.visitLocation}</h3>
        <p className="text-lg text-gray-600">8536 SE 82nd Ave, Portland, OR 97266</p>
      </div>
      <div className="bg-gray-200 h-96 rounded-lg flex items-center justify-center">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2796.8567891234567!2d-122.57895!3d45.46123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x5495a0b91234567%3A0x1234567890abcdef!2s8536%20SE%2082nd%20Ave%2C%20Portland%2C%20OR%2097266!5e0!3m2!1sen!2sus!4v1234567890123"
          width="100%"
          height="100%"
          style={{ border: 0 }}
          allowFullScreen
          loading="lazy"
          referrerPolicy="no-referrer-when-downgrade"
          className="rounded-lg"
        ></iframe>
      </div>
    </div>
  );
};

export default LocationMap;
