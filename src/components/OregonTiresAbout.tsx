
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
    <section id="about" className="py-16 bg-white">
      {/* About anchor for navigation */}
      <div className="pt-8">
        <div className="container mx-auto px-4">
          <div className="max-w-4xl mx-auto text-center">
            <h2 className="text-4xl font-bold mb-6" style={{ color: primaryColor }}>
              About Oregon Tires
            </h2>
            <h3 className="text-3xl font-semibold mb-4" style={{ color: primaryColor }}>
              {t.aboutTitle}
            </h3>
            <p className="text-xl text-gray-600 mb-8">{t.aboutSubtitle}</p>
            
            {/* Company description */}
            <div className="mb-12">
              <Card className="border-0" style={{ backgroundColor: `${primaryColor}15` }}>
                <CardContent className="p-8">
                  <p className="text-lg text-gray-700 leading-relaxed">
                    At Oregon Tires, we are dedicated to offering high-quality new and used tires, reliable mechanical services, and personalized attention. We work every day to provide fast and effective solutions that ensure the best performance and safety for our customers' vehicles. We aim to be the trusted tire shop in Portland. We want to be recognized for our quality, honesty, and prompt service that keeps our customers safe and satisfied on the road.
                  </p>
                </CardContent>
              </Card>
            </div>

            <div className="grid md:grid-cols-2 gap-8 text-left">
              <div>
                <h3 className="text-2xl font-semibold mb-4" style={{ color: primaryColor }}>
                  {t.ourMission}
                </h3>
                <p className="text-gray-600 mb-4">{t.missionText}</p>
              </div>
              <div>
                <h3 className="text-2xl font-semibold mb-4" style={{ color: primaryColor }}>
                  {t.whyChooseUs}
                </h3>
                <ul className="text-gray-600 space-y-2">
                  <li>• {t.bilingualStaff}</li>
                  <li>• {t.honestPricing}</li>
                  <li>• {t.qualityWorkmanship}</li>
                  <li>• {t.fastReliableService}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresAbout;
