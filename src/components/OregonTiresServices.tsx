
import React from 'react';
import { CheckCircle } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useServiceImagesForFrontend } from '@/hooks/useServiceImagesForFrontend';

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
  const { getImageUrl, getImageStyle } = useServiceImagesForFrontend();

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
          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 h-72">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('expert-technicians')})`,
                ...getImageStyle('expert-technicians')
              }}
            >
              <div className="absolute inset-0 bg-black/40"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col justify-end p-6">
              <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                <CardTitle className="text-lg mb-2" style={{ color: primaryColor }}>{t.expertService}</CardTitle>
                <p className="text-gray-700 text-sm">{t.expertServiceDesc}</p>
              </div>
            </div>
          </Card>

          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 h-72">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('fast-cars')})`,
                ...getImageStyle('fast-cars')
              }}
            >
              <div className="absolute inset-0 bg-black/40"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col justify-end p-6">
              <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                <CardTitle className="text-lg mb-2" style={{ color: primaryColor }}>{t.quickService}</CardTitle>
                <p className="text-gray-700 text-sm">{t.quickServiceDesc}</p>
              </div>
            </div>
          </Card>

          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 h-72">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('quality-car-parts')})`,
                ...getImageStyle('quality-car-parts')
              }}
            >
              <div className="absolute inset-0 bg-black/40"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col justify-end p-6">
              <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                <CardTitle className="text-lg mb-2" style={{ color: primaryColor }}>{t.qualityParts}</CardTitle>
                <p className="text-gray-700 text-sm">{t.qualityPartsDesc}</p>
              </div>
            </div>
          </Card>

          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 h-72">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('bilingual-support')})`,
                ...getImageStyle('bilingual-support')
              }}
            >
              <div className="absolute inset-0 bg-black/40"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col justify-end p-6">
              <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                <CardTitle className="text-lg mb-2" style={{ color: primaryColor }}>{t.bilingualService}</CardTitle>
                <p className="text-gray-700 text-sm">{t.bilingualServiceDesc}</p>
              </div>
            </div>
          </Card>
        </div>

        {/* Detailed Services */}
        <div className="grid md:grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Tire Services */}
          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 min-h-[500px]">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('tire-shop')})`,
                ...getImageStyle('tire-shop')
              }}
            >
              <div className="absolute inset-0 bg-black/50"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col">
              <div className="bg-white/95 backdrop-blur-sm m-4 rounded-lg p-4">
                <CardTitle className="text-xl flex items-center gap-2 mb-2" style={{ color: primaryColor }}>
                  {t.tireServices}
                </CardTitle>
              </div>
              <div className="flex-1 p-4">
                <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4 space-y-3">
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.newOrUsedTires}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.newOrUsedTiresDesc}</p>
                  </div>
                  
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.mountAndBalance}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.mountAndBalanceDesc}</p>
                  </div>

                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.tireRepairService}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.tireRepairServiceDesc}</p>
                  </div>
                </div>
              </div>
            </div>
          </Card>

          {/* Auto Maintenance */}
          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 min-h-[500px]">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('auto-repair')})`,
                ...getImageStyle('auto-repair')
              }}
            >
              <div className="absolute inset-0 bg-black/50"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col">
              <div className="bg-white/95 backdrop-blur-sm m-4 rounded-lg p-4">
                <CardTitle className="text-xl flex items-center gap-2 mb-2" style={{ color: primaryColor }}>
                  {t.autoMaintenance}
                </CardTitle>
              </div>
              <div className="flex-1 p-4">
                <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4 space-y-3">
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.oilChangeService}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.oilChangeServiceDesc}</p>
                  </div>

                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.brakeServices}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.brakeServicesDesc}</p>
                  </div>

                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.tuneup}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.tuneupDesc}</p>
                  </div>
                </div>
              </div>
            </div>
          </Card>

          {/* Specialized Services */}
          <Card className="relative overflow-hidden hover:shadow-lg transition-shadow border-0 min-h-[500px]">
            <div 
              className="absolute inset-0 bg-cover bg-center transition-all duration-300"
              style={{ 
                backgroundImage: `url(${getImageUrl('specialized-tools')})`,
                ...getImageStyle('specialized-tools')
              }}
            >
              <div className="absolute inset-0 bg-black/50"></div>
            </div>
            <div className="relative z-10 h-full flex flex-col">
              <div className="bg-white/95 backdrop-blur-sm m-4 rounded-lg p-4">
                <CardTitle className="text-xl flex items-center gap-2 mb-2" style={{ color: primaryColor }}>
                  {t.specializedServices}
                </CardTitle>
              </div>
              <div className="flex-1 p-4">
                <div className="bg-white/95 backdrop-blur-sm rounded-lg p-4 space-y-3">
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.alignment}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.alignmentDesc}</p>
                  </div>

                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.mechanicalInspection}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.mechanicalInspectionDesc}</p>
                  </div>

                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.homeService}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.homeServiceDesc}</p>
                  </div>

                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1 flex items-center gap-2">
                      <CheckCircle className="h-4 w-4" style={{ color: primaryColor }} />
                      {t.roadsideAssistance}
                    </h4>
                    <p className="text-gray-600 text-sm">{t.roadsideAssistanceDesc}</p>
                  </div>

                  <div className="p-3 bg-red-50 rounded-lg border-l-4 border-red-500 mt-4">
                    <p className="text-red-700 font-semibold text-sm mb-1">{t.needServiceToday}</p>
                    <p className="text-red-600 text-sm">{t.callImmediately}</p>
                    <p className="text-red-600 text-xs mt-1">{t.availableHours}</p>
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </div>

        {/* Service Promise */}
        <div className="mt-16 text-center">
          <Card className="max-w-4xl mx-auto border-0" style={{ backgroundColor: `${primaryColor}15` }}>
            <CardContent className="p-8">
              <h3 className="text-2xl font-bold mb-4" style={{ color: primaryColor }}>{t.ourServicePromise}</h3>
              <p className="text-gray-700 text-lg leading-relaxed mb-6">
                {t.servicePromiseDesc}
              </p>
              <div className="grid md:grid-cols-3 gap-6 text-center">
                <div>
                  <div className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>✓</div>
                  <div className="font-semibold text-gray-800">{t.qualityGuarantee}</div>
                </div>
                <div>
                  <div className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>✓</div>
                  <div className="font-semibold text-gray-800">{t.fairPricing}</div>
                </div>
                <div>
                  <div className="text-2xl font-bold mb-2" style={{ color: primaryColor }}>✓</div>
                  <div className="font-semibold text-gray-800">{t.expertServicePromise}</div>
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
