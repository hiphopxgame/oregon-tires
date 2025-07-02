
import React from 'react';
import { Card, CardContent } from "@/components/ui/card";

interface AboutProps {
  translations: any;
  primaryColor: string;
  secondaryColor: string;
}

const OregonTiresAbout: React.FC<AboutProps> = ({
  translations,
  primaryColor,
  secondaryColor
}) => {
  const t = translations;

  return (
    <section id="about" className="py-16 bg-white scroll-mt-24">
      <div className="container mx-auto px-4">
        <div className="max-w-6xl mx-auto">
          <div className="text-center mb-12">
            <h2 className="text-4xl font-bold mb-6" style={{ color: primaryColor }}>
              About Oregon Tires
            </h2>
            <p className="text-2xl font-semibold mb-8" style={{ color: primaryColor }}>
              "We take care of your car, you enjoy the road."
            </p>
          </div>

          {/* Vision Section */}
          <div className="mb-12">
            <Card className="border-0" style={{ backgroundColor: `${primaryColor}10` }}>
              <CardContent className="p-8">
                <h3 className="text-3xl font-bold mb-4 text-center" style={{ color: primaryColor }}>
                  {t.vision}
                </h3>
                <p className="text-lg text-gray-700 leading-relaxed text-center">
                  {t.visionText}
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Mission Section */}
          <div className="mb-12">
            <Card className="border-0" style={{ backgroundColor: `${primaryColor}08` }}>
              <CardContent className="p-8">
                <h3 className="text-3xl font-bold mb-4 text-center" style={{ color: primaryColor }}>
                  {t.mission}
                </h3>
                <p className="text-lg text-gray-700 leading-relaxed text-center">
                  {t.missionText}
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Goals Section */}
          <div className="mb-12">
            <h3 className="text-3xl font-bold mb-8 text-center" style={{ color: primaryColor }}>
              {t.goals}
            </h3>
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
              <Card className="h-full">
                <CardContent className="p-6">
                  <p className="text-gray-700">
                    To offer quality service that exceeds our customers' expectations with every visit.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="h-full">
                <CardContent className="p-6">
                  <p className="text-gray-700">
                    Maintain a varied stock of new and used tires, ensuring immediate availability.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="h-full">
                <CardContent className="p-6">
                  <p className="text-gray-700">
                    Provide preventive and corrective mechanical services efficiently and safely.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="h-full">
                <CardContent className="p-6">
                  <p className="text-gray-700">
                    Foster long-term relationships with our customers through trust and continued satisfaction.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="h-full">
                <CardContent className="p-6">
                  <p className="text-gray-700">
                    Continuously train our team to stay ahead in automotive technology and best practices.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="h-full">
                <CardContent className="p-6">
                  <p className="text-gray-700">
                    Implement promotions and loyalty programs that reward our customers' preference.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="h-full md:col-span-2 lg:col-span-3">
                <CardContent className="p-6">
                  <p className="text-gray-700 text-center">
                    Operate in a sustainable and environmentally responsible manner.
                  </p>
                </CardContent>
              </Card>
            </div>
          </div>

          {/* Why Choose Us & Experience Sections - Side by Side */}
          <div className="mb-8 grid md:grid-cols-2 gap-6">
            {/* Why Choose Us Section */}
            <Card className="border-0 shadow-lg" style={{ backgroundColor: `${primaryColor}05` }}>
              <CardContent className="p-8">
                <div className="text-center">
                  <h3 className="text-2xl font-semibold mb-4" style={{ color: primaryColor }}>
                    {t.whyChooseUs}
                  </h3>
                  <ul className="text-gray-600 space-y-2 inline-block text-left">
                    <li>• Bilingual staff (English & Spanish)</li>
                    <li>• Honest, transparent pricing</li>
                    <li>• Quality workmanship guaranteed</li>
                    <li>• Fast and reliable service</li>
                  </ul>
                </div>
              </CardContent>
            </Card>

            {/* Experience Section */}
            <Card className="border-0 shadow-lg" style={{ backgroundColor: `${primaryColor}08` }}>
              <CardContent className="p-8">
                <div className="text-center">
                  <h3 className="text-2xl font-semibold mb-4" style={{ color: primaryColor }}>
                    {t.servingPortland}
                  </h3>
                  <p className="text-gray-700 text-lg leading-relaxed">
                    {t.servingPortlandText}
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresAbout;
