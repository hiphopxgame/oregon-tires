
import React from 'react';

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
      <div className="container mx-auto px-4">
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-4xl font-bold mb-6" style={{ color: primaryColor }}>
            {t.aboutTitle}
          </h2>
          <p className="text-xl text-gray-600 mb-8">{t.aboutSubtitle}</p>
          
          <div className="grid md:grid-cols-2 gap-8 text-left mb-12">
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
                <li>• {t.fastService}</li>
              </ul>
            </div>
          </div>

          <div className="mb-8">
            <h3 className="text-2xl font-semibold mb-4" style={{ color: primaryColor }}>
              {t.ourCommitment}
            </h3>
            <div className="text-gray-600 space-y-3 max-w-3xl mx-auto">
              <p>• {t.commitment1}</p>
              <p>• {t.commitment2}</p>
              <p>• {t.commitment3}</p>
              <p>• {t.commitment4}</p>
            </div>
          </div>

          <div className="bg-gray-50 rounded-lg p-6 max-w-3xl mx-auto">
            <div className="flex items-center justify-center gap-6">
              <img 
                src="/lovable-uploads/a6d722d4-02c0-4283-bb68-2c206fd7ef55.png" 
                alt="Oregon Tires Auto Care Logo" 
                className="h-16 w-16 flex-shrink-0"
              />
              <div className="text-center">
                <h3 className="text-xl font-bold mb-2" style={{ color: primaryColor }}>
                  Serving Portland Since 2008
                </h3>
                <p className="text-gray-600">
                  With over 15 years of experience serving the Portland community, we have built our reputation on honest service, quality workmanship, and treating every customer like family.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresAbout;
