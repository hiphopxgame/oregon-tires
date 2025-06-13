
import React from 'react';
import { Check, Star, Clock } from 'lucide-react';

interface AboutProps {
  translations: any;
  primaryColor: string;
}

const OregonTiresAbout: React.FC<AboutProps> = ({ translations, primaryColor }) => {
  const t = translations;

  return (
    <section id="about" className="py-16">
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>{t.about}</h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto">
            {t.aboutSubtitle}
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
          <div className="text-center">
            <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <Check className="h-8 w-8" style={{ color: primaryColor }} />
            </div>
            <h3 className="text-xl font-bold mb-2" style={{ color: primaryColor }}>Expert Team</h3>
            <p className="text-gray-600">Over 15 years of automotive experience with certified technicians</p>
          </div>

          <div className="text-center">
            <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <Star className="h-8 w-8" style={{ color: primaryColor }} />
            </div>
            <h3 className="text-xl font-bold mb-2" style={{ color: primaryColor }}>Quality Service</h3>
            <p className="text-gray-600">We use only premium parts and provide warranty on all our work</p>
          </div>

          <div className="text-center">
            <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <Clock className="h-8 w-8" style={{ color: primaryColor }} />
            </div>
            <h3 className="text-xl font-bold mb-2" style={{ color: primaryColor }}>Fast Service</h3>
            <p className="text-gray-600">Most services completed same day with competitive pricing</p>
          </div>

          <div className="text-center">
            <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <Check className="h-8 w-8" style={{ color: primaryColor }} />
            </div>
            <h3 className="text-xl font-bold mb-2" style={{ color: primaryColor }}>Full Service</h3>
            <p className="text-gray-600">From tire installation to complete automotive repair solutions</p>
          </div>
        </div>

        <div className="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <h3 className="text-3xl font-bold mb-6" style={{ color: primaryColor }}>Our Story</h3>
            <p className="text-gray-600 mb-4">
              Oregon Tires has been serving the Portland community since 2008. What started as a small tire shop has grown into a full-service automotive center, but we've never forgotten our roots or our commitment to treating every customer like family.
            </p>
            <p className="text-gray-600 mb-6">
              We believe in honest, transparent service. Our certified technicians will always explain what your vehicle needs and why, giving you the information you need to make the best decision for your safety and budget.
            </p>

            <div className="grid grid-cols-2 gap-8">
              <div className="text-center">
                <div className="text-4xl font-bold mb-2" style={{ color: primaryColor }}>15+</div>
                <div className="text-gray-600">Years Experience</div>
              </div>
              <div className="text-center">
                <div className="text-4xl font-bold mb-2" style={{ color: primaryColor }}>5000+</div>
                <div className="text-gray-600">Happy Customers</div>
              </div>
            </div>
          </div>

          <div>
            <h3 className="text-3xl font-bold mb-6" style={{ color: primaryColor }}>Why Choose Us?</h3>
            <ul className="space-y-4">
              <li className="flex items-center gap-3">
                <Check className="h-5 w-5" style={{ color: primaryColor }} />
                <span className="text-gray-700">ASE Certified Technicians</span>
              </li>
              <li className="flex items-center gap-3">
                <Check className="h-5 w-5" style={{ color: primaryColor }} />
                <span className="text-gray-700">Same Day Service Available</span>
              </li>
              <li className="flex items-center gap-3">
                <Check className="h-5 w-5" style={{ color: primaryColor }} />
                <span className="text-gray-700">Family Owned & Operated</span>
              </li>
              <li className="flex items-center gap-3">
                <Check className="h-5 w-5" style={{ color: primaryColor }} />
                <span className="text-gray-700">Comprehensive High Quality Auto Care</span>
              </li>
            </ul>

            <div className="mt-8 p-6 bg-green-50 rounded-lg">
              <h4 className="text-xl font-bold mb-2" style={{ color: primaryColor }}>Our Promise</h4>
              <p className="text-gray-600">
                We guarantee quality workmanship and stand behind every service with our comprehensive warranty. Your satisfaction is our top priority.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresAbout;
