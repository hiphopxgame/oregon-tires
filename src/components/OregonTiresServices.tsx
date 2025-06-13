
import React from 'react';
import { Wrench, Clock, Shield, Users, Car, Settings, Zap } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

interface ServicesProps {
  translations: any;
  primaryColor: string;
  secondaryColor: string;
}

const OregonTiresServices: React.FC<ServicesProps> = ({
  translations,
  primaryColor,
  secondaryColor
}) => {
  const t = translations;

  return (
    <section className="py-16 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>
            {t.services}
          </h2>
          <p className="text-xl text-gray-600">{t.servicesSubtitle}</p>
        </div>

        {/* Key Features */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
          <Card className="text-center hover:shadow-lg transition-shadow">
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

          <Card className="text-center hover:shadow-lg transition-shadow">
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

          <Card className="text-center hover:shadow-lg transition-shadow">
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

          <Card className="text-center hover:shadow-lg transition-shadow">
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
        <div className="grid md:grid-cols-3 gap-8">
          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <div className="flex items-center gap-3 mb-4">
                <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center">
                  <Car className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <CardTitle style={{ color: primaryColor }}>{t.tireServices}</CardTitle>
              </div>
            </CardHeader>
            <CardContent>
              <ul className="space-y-3 text-gray-600">
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.tireInstallation}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.tireRepair}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.tireRotation}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.wheelAlignment}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.tireBalancing}
                </li>
              </ul>
            </CardContent>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <div className="flex items-center gap-3 mb-4">
                <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center">
                  <Settings className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <CardTitle style={{ color: primaryColor }}>{t.autoMaintenance}</CardTitle>
              </div>
            </CardHeader>
            <CardContent>
              <ul className="space-y-3 text-gray-600">
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.oilChange}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.brakeService}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.batteryService}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.engineDiagnostics}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.fluidChecks}
                </li>
              </ul>
            </CardContent>
          </Card>

          <Card className="hover:shadow-lg transition-shadow">
            <CardHeader>
              <div className="flex items-center gap-3 mb-4">
                <div className="bg-red-100 w-12 h-12 rounded-lg flex items-center justify-center">
                  <Zap className="h-6 w-6 text-red-600" />
                </div>
                <CardTitle style={{ color: primaryColor }}>{t.emergencyService}</CardTitle>
              </div>
            </CardHeader>
            <CardContent>
              <ul className="space-y-3 text-gray-600 mb-4">
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.roadSideAssistance}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.flatTireRepair}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.jumpStart}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.emergencyTowing}
                </li>
                <li className="flex items-center gap-2">
                  <div className="w-2 h-2 rounded-full" style={{ backgroundColor: primaryColor }}></div>
                  {t.lockoutService}
                </li>
              </ul>
              <div className="p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
                <p className="text-red-700 font-semibold text-sm">{t.needServiceToday}</p>
                <p className="text-red-600 text-sm mt-1">Call us at (503) 367-9714</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresServices;
