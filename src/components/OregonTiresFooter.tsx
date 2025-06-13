
import React from 'react';
import { Phone, MapPin } from 'lucide-react';

interface FooterProps {
  primaryColor: string;
}

const OregonTiresFooter: React.FC<FooterProps> = ({ primaryColor }) => {
  return (
    <footer className="py-12" style={{ backgroundColor: primaryColor }}>
      <div className="container mx-auto px-4">
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
          <div>
            <div className="flex items-center gap-4 mb-4">
              <img 
                src="/lovable-uploads/f000a232-32e4-4f91-8b69-f7e61ac811f2.png" 
                alt="Oregon Tires Logo" 
                className="h-12 w-12"
              />
              <div>
                <h3 className="text-white font-bold text-lg">Oregon Tires</h3>
                <p className="text-white text-sm">High Quality Auto Care</p>
              </div>
            </div>
            <p className="text-white text-sm mb-4">
              Your trusted automotive service center in Portland, Oregon. Professional service in English and Spanish for over 15 years.
            </p>
            <div className="text-white text-sm">
              <span className="bg-yellow-400 text-black px-2 py-1 rounded text-xs font-bold">5-Star Service</span>
            </div>
          </div>

          <div>
            <h4 className="text-white font-bold mb-4">Contact Information</h4>
            <div className="space-y-2 text-white text-sm">
              <div className="flex items-center gap-2">
                <Phone className="h-4 w-4" />
                <div>
                  <div>(503) 367-9714</div>
                  <div className="text-xs text-gray-300">Call for service</div>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <MapPin className="h-4 w-4" />
                <div>
                  <div>8536 SE 82nd Ave, Portland, OR 97266</div>
                  <div className="text-xs text-gray-300">Visit our location</div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <h4 className="text-white font-bold mb-4">Business Hours</h4>
            <div className="text-white text-sm space-y-1">
              <div>Monday - Saturday:</div>
              <div className="font-semibold">7AM - 7PM</div>
              <div>Sunday:</div>
              <div className="font-semibold">Closed</div>
              <div className="text-yellow-400 text-xs mt-2">Same Day Service Available</div>
            </div>
          </div>

          <div>
            <h4 className="text-white font-bold mb-4">Our Services</h4>
            <div className="text-white text-sm space-y-1">
              <div>• Tire Sales & Installation</div>
              <div>• Brake Services</div>
              <div>• Oil Changes</div>
              <div>• Vehicle Maintenance</div>
              <div>• Automotive Repairs</div>
              <div className="mt-2">🇺🇸 English & 🇲🇽 Spanish Speaking</div>
            </div>
          </div>
        </div>

        <div className="border-t border-green-600 pt-8 text-center text-white text-sm">
          <p>© 2025 Oregon Tires High Quality Auto Care. All rights reserved.</p>
          <p className="mt-2">Licensed & Insured • Serving Portland Since 2009</p>
        </div>
      </div>
    </footer>
  );
};

export default OregonTiresFooter;
