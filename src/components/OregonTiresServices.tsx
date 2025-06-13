
import React from 'react';
import { Wrench, Clock, Shield, Users } from 'lucide-react';
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface ServicesProps {
  translations: any;
  primaryColor: string;
  secondaryColor: string;
  openContactForm: () => void;
}

const OregonTiresServices: React.FC<ServicesProps> = ({
  translations,
  primaryColor,
  secondaryColor,
  openContactForm
}) => {
  const t = translations;

  return (
    <section id="services" className="py-16 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>
            {t.services}
          </h2>
          <p className="text-xl text-gray-600">{t.servicesSubtitle}</p>
        </div>

        {/* Key Features */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
          <Card className="text-center">
            <CardHeader>
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Wrench className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle style={{ color: primaryColor }}>{t.expertService}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">{t.expertServiceDesc}</p>
            </CardContent>
          </Card>

          <Card className="text-center">
            <CardHeader>
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Clock className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle style={{ color: primaryColor }}>{t.quickService}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">{t.quickServiceDesc}</p>
            </CardContent>
          </Card>

          <Card className="text-center">
            <CardHeader>
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Shield className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle style={{ color: primaryColor }}>{t.qualityParts}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">{t.qualityPartsDesc}</p>
            </CardContent>
          </Card>

          <Card className="text-center">
            <CardHeader>
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Users className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle style={{ color: primaryColor }}>{t.bilingualService}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600">{t.bilingualServiceDesc}</p>
            </CardContent>
          </Card>
        </div>

        {/* Service Categories */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <Card>
            <CardHeader>
              <CardTitle style={{ color: primaryColor }}>{t.tireServices}</CardTitle>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-gray-600">
                <li>• {t.tireInstallation}</li>
                <li>• {t.tireRepair}</li>
                <li>• {t.tireRotation}</li>
                <li>• {t.wheelAlignment}</li>
                <li>• {t.tireBalancing}</li>
              </ul>
              <Button 
                className="mt-4 w-full text-white"
                style={{ backgroundColor: primaryColor }}
                onClick={openContactForm}
              >
                {t.contactUs}
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle style={{ color: primaryColor }}>{t.autoMaintenance}</CardTitle>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-gray-600">
                <li>• {t.oilChange}</li>
                <li>• {t.brakeService}</li>
                <li>• {t.batteryService}</li>
                <li>• {t.engineDiagnostics}</li>
                <li>• {t.fluidChecks}</li>
              </ul>
              <Button 
                className="mt-4 w-full text-white"
                style={{ backgroundColor: primaryColor }}
                onClick={openContactForm}
              >
                {t.contactUs}
              </Button>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle style={{ color: primaryColor }}>{t.emergencyService}</CardTitle>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-gray-600">
                <li>• {t.roadSideAssistance}</li>
                <li>• {t.flatTireRepair}</li>
                <li>• {t.jumpStart}</li>
                <li>• {t.emergencyTowing}</li>
                <li>• {t.lockoutService}</li>
              </ul>
              <div className="mt-4 p-4 bg-red-50 rounded-lg">
                <p className="text-red-700 font-semibold mb-2">{t.needServiceToday}</p>
                <Button 
                  className="w-full text-white"
                  style={{ backgroundColor: primaryColor }}
                  onClick={openContactForm}
                >
                  {t.scheduleService}
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresServices;
