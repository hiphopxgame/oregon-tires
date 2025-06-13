
import React from 'react';
import { Phone, Calendar, Check } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface ServicesProps {
  translations: any;
  primaryColor: string;
  openScheduleForm: () => void;
}

const OregonTiresServices: React.FC<ServicesProps> = ({
  translations,
  primaryColor,
  openScheduleForm
}) => {
  const t = translations;

  return (
    <section id="services" className="py-16 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>{t.services}</h2>
          <p className="text-xl text-gray-600">{t.servicesSubtitle}</p>
        </div>

        <div className="grid md:grid-cols-3 gap-8">
          <Card className="border-2 hover:shadow-lg transition-shadow">
            <CardHeader>
              <CardTitle style={{ color: primaryColor }}>Tire Sales & Installation</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 mb-4">Premium tire brands including Michelin, Goodyear, Bridgestone, and more. Professional mounting, balancing, and alignment services.</p>
              <ul className="space-y-2">
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>New & Used Tires</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Tire Repair</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Wheel Alignment</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Tire Rotation</span>
                </li>
              </ul>
            </CardContent>
          </Card>

          <Card className="border-2 hover:shadow-lg transition-shadow">
            <CardHeader>
              <CardTitle style={{ color: primaryColor }}>Brake Services</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 mb-4">Complete brake system services including inspection, pad replacement, rotor resurfacing, and brake fluid changes.</p>
              <ul className="space-y-2">
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Brake Inspection</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Pad Replacement</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Rotor Service</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Brake Fluid</span>
                </li>
              </ul>
            </CardContent>
          </Card>

          <Card className="border-2 hover:shadow-lg transition-shadow">
            <CardHeader>
              <CardTitle style={{ color: primaryColor }}>Oil Changes & Maintenance</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 mb-4">Regular maintenance services to keep your vehicle running at peak performance with quality oils and filters.</p>
              <ul className="space-y-2">
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Oil Changes</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Filter Replacement</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Fluid Checks</span>
                </li>
                <li className="flex items-center gap-2">
                  <Check className="h-4 w-4" style={{ color: primaryColor }} />
                  <span>Multi-Point Inspection</span>
                </li>
              </ul>
            </CardContent>
          </Card>
        </div>

        <div className="text-center mt-12">
          <div className="bg-white p-8 rounded-lg shadow-lg inline-block">
            <h3 className="text-2xl font-bold mb-4" style={{ color: primaryColor }}>{t.needServiceTitle}</h3>
            <p className="text-gray-600 mb-6">{t.needServiceSubtitle}</p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button className="text-white" style={{ backgroundColor: primaryColor }}>
                <Phone className="h-4 w-4 mr-2" />
                {t.callButton}
              </Button>
              <Button 
                variant="outline" 
                style={{ borderColor: primaryColor, color: primaryColor }}
                onClick={openScheduleForm}
              >
                <Calendar className="h-4 w-4 mr-2" />
                {t.scheduleServiceButton}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresServices;
