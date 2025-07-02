
import React from 'react';
import { Wrench, Clock, Shield, Users, Car, Settings, Zap, CheckCircle } from 'lucide-react';
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
    <section id="services" className="py-16 bg-gray-50 scroll-mt-24">
      <div className="container mx-auto px-4">
        <div className="text-center mb-16">
          <h2 className="text-4xl font-bold mb-6" style={{ color: primaryColor }}>
            {t.services}
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">{t.servicesSubtitle}</p>
        </div>

        {/* Key Features */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
          <Card className="text-center hover:shadow-lg transition-shadow border-0">
            <CardHeader className="pb-4">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Wrench className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle className="text-lg" style={{ color: primaryColor }}>{t.expertService}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 text-sm">{t.expertServiceDesc}</p>
            </CardContent>
          </Card>

          <Card className="text-center hover:shadow-lg transition-shadow border-0">
            <CardHeader className="pb-4">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Clock className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle className="text-lg" style={{ color: primaryColor }}>{t.quickService}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 text-sm">{t.quickServiceDesc}</p>
            </CardContent>
          </Card>

          <Card className="text-center hover:shadow-lg transition-shadow border-0">
            <CardHeader className="pb-4">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Shield className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle className="text-lg" style={{ color: primaryColor }}>{t.qualityParts}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 text-sm">{t.qualityPartsDesc}</p>
            </CardContent>
          </Card>

          <Card className="text-center hover:shadow-lg transition-shadow border-0">
            <CardHeader className="pb-4">
              <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Users className="h-8 w-8" style={{ color: primaryColor }} />
              </div>
              <CardTitle className="text-lg" style={{ color: primaryColor }}>{t.bilingualService}</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-gray-600 text-sm">{t.bilingualServiceDesc}</p>
            </CardContent>
          </Card>
        </div>

        {/* Detailed Services */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          {/* Tire Services */}
          <Card className="hover:shadow-lg transition-shadow border-0">
            <CardHeader>
              <div className="flex items-center gap-3 mb-4">
                <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center">
                  <Car className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <CardTitle className="text-xl" style={{ color: primaryColor }}>{t.tireServices}</CardTitle>
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.newOrUsedTires}
                </h4>
                <p className="text-gray-600 text-sm">{t.newOrUsedTiresDesc}</p>
              </div>
              
              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.mountAndBalance}
                </h4>
                <p className="text-gray-600 text-sm">{t.mountAndBalanceDesc}</p>
              </div>

              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.tireRepairService}
                </h4>
                <p className="text-gray-600 text-sm">{t.tireRepairServiceDesc}</p>
              </div>
            </CardContent>
          </Card>

          {/* Auto Maintenance */}
          <Card className="hover:shadow-lg transition-shadow border-0">
            <CardHeader>
              <div className="flex items-center gap-3 mb-4">
                <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center">
                  <Settings className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <CardTitle className="text-xl" style={{ color: primaryColor }}>{t.autoMaintenance}</CardTitle>
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.oilChangeService}
                </h4>
                <p className="text-gray-600 text-sm">{t.oilChangeServiceDesc}</p>
              </div>

              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.brakeServices}
                </h4>
                <p className="text-gray-600 text-sm">{t.brakeServicesDesc}</p>
              </div>

              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.tuneup}
                </h4>
                <p className="text-gray-600 text-sm">{t.tuneupDesc}</p>
              </div>
            </CardContent>
          </Card>

          {/* Specialized Services */}
          <Card className="hover:shadow-lg transition-shadow border-0">
            <CardHeader>
              <div className="flex items-center gap-3 mb-4">
                <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center">
                  <Zap className="h-6 w-6" style={{ color: primaryColor }} />
                </div>
                <CardTitle className="text-xl" style={{ color: primaryColor }}>{t.specializedServices}</CardTitle>
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.alignment}
                </h4>
                <p className="text-gray-600 text-sm">{t.alignmentDesc}</p>
              </div>

              <div>
                <h4 className="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                  <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                  {t.mechanicalInspection}
                </h4>
                <p className="text-gray-600 text-sm">{t.mechanicalInspectionDesc}</p>
              </div>

              <div className="p-4 bg-red-50 rounded-lg border-l-4 border-red-500 mt-6">
                <p className="text-red-700 font-semibold text-sm mb-2">{t.needServiceToday}</p>
                <p className="text-red-600 text-sm">Call us immediately at (503) 367-9714</p>
                <p className="text-red-600 text-xs mt-1">Available Mon-Sat 7AM-7PM</p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Service Promise */}
        <div className="mt-16 text-center">
          <Card className="max-w-4xl mx-auto border-0" style={{ backgroundColor: `${primaryColor}15` }}>
            <CardContent className="p-8">
              <h3 className="text-2xl font-bold mb-4" style={{ color: primaryColor }}>Our Service Promise</h3>
              <p className="text-gray-700 text-lg leading-relaxed mb-6">
                Every service comes with our commitment to quality, transparency, and customer satisfaction. 
                We provide detailed estimates, use only premium parts, and back our work with comprehensive warranties.
              </p>
              <div className="grid md:grid-cols-3 gap-6 text-center">
                <div>
                  <div className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>✓</div>
                  <div className="font-semibold text-gray-800">Quality Guarantee</div>
                </div>
                <div>
                  <div className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>✓</div>
                  <div className="font-semibold text-gray-800">Fair Pricing</div>
                </div>
                <div>
                  <div className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>✓</div>
                  <div className="font-semibold text-gray-800">Expert Service</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresServices;
