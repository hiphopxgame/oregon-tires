
import React from 'react';
import { Check, Star, Clock, Award } from 'lucide-react';
import { Card, CardContent } from "@/components/ui/card";

interface AboutProps {
  translations: any;
  primaryColor: string;
}

const OregonTiresAbout: React.FC<AboutProps> = ({ translations, primaryColor }) => {
  const t = translations;

  return (
    <section id="about" className="py-16 bg-white">
      <div className="container mx-auto px-4">
        <div className="text-center mb-16">
          <h2 className="text-4xl font-bold mb-6" style={{ color: primaryColor }}>{t.about}</h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            {t.aboutSubtitle}
          </p>
        </div>

        {/* Key Features Cards */}
        <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
          <Card className="text-center p-6 hover:shadow-lg transition-shadow border-0 bg-gray-50">
            <CardContent className="p-0">
              <div className="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <Award className="h-10 w-10" style={{ color: primaryColor }} />
              </div>
              <h3 className="text-xl font-bold mb-4" style={{ color: primaryColor }}>Expert Team</h3>
              <p className="text-gray-600 leading-relaxed">Over 15 years of automotive experience with ASE certified technicians</p>
            </CardContent>
          </Card>

          <Card className="text-center p-6 hover:shadow-lg transition-shadow border-0 bg-gray-50">
            <CardContent className="p-0">
              <div className="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <Star className="h-10 w-10" style={{ color: primaryColor }} />
              </div>
              <h3 className="text-xl font-bold mb-4" style={{ color: primaryColor }}>Quality Service</h3>
              <p className="text-gray-600 leading-relaxed">Premium parts and comprehensive warranty on all our work</p>
            </CardContent>
          </Card>

          <Card className="text-center p-6 hover:shadow-lg transition-shadow border-0 bg-gray-50">
            <CardContent className="p-0">
              <div className="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <Clock className="h-10 w-10" style={{ color: primaryColor }} />
              </div>
              <h3 className="text-xl font-bold mb-4" style={{ color: primaryColor }}>Fast Service</h3>
              <p className="text-gray-600 leading-relaxed">Most services completed same day with competitive pricing</p>
            </CardContent>
          </Card>

          <Card className="text-center p-6 hover:shadow-lg transition-shadow border-0 bg-gray-50">
            <CardContent className="p-0">
              <div className="bg-green-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <Check className="h-10 w-10" style={{ color: primaryColor }} />
              </div>
              <h3 className="text-xl font-bold mb-4" style={{ color: primaryColor }}>Full Service</h3>
              <p className="text-gray-600 leading-relaxed">Complete automotive solutions from tires to maintenance</p>
            </CardContent>
          </Card>
        </div>

        {/* Main Content */}
        <div className="grid lg:grid-cols-2 gap-16 items-start">
          <div className="space-y-8">
            <div>
              <h3 className="text-3xl font-bold mb-6" style={{ color: primaryColor }}>Our Story</h3>
              <div className="space-y-4 text-gray-700 leading-relaxed">
                <p>
                  Oregon Tires has been serving the Portland community since 2008. What started as a small tire shop has grown into a full-service automotive center, but we've never forgotten our roots or our commitment to treating every customer like family.
                </p>
                <p>
                  We believe in honest, transparent service. Our certified technicians will always explain what your vehicle needs and why, giving you the information you need to make the best decision for your safety and budget.
                </p>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-8 pt-6">
              <div className="text-center">
                <div className="text-5xl font-bold mb-3" style={{ color: primaryColor }}>15+</div>
                <div className="text-gray-600 font-medium">Years Experience</div>
              </div>
              <div className="text-center">
                <div className="text-5xl font-bold mb-3" style={{ color: primaryColor }}>5000+</div>
                <div className="text-gray-600 font-medium">Happy Customers</div>
              </div>
            </div>
          </div>

          <div className="space-y-8">
            <div>
              <h3 className="text-3xl font-bold mb-6" style={{ color: primaryColor }}>Why Choose Us?</h3>
              <div className="space-y-4">
                <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                  <Check className="h-6 w-6 mt-1 flex-shrink-0" style={{ color: primaryColor }} />
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1">ASE Certified Technicians</h4>
                    <p className="text-gray-600 text-sm">Our team holds industry certifications and stays current with automotive technology.</p>
                  </div>
                </div>
                <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                  <Check className="h-6 w-6 mt-1 flex-shrink-0" style={{ color: primaryColor }} />
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1">Same Day Service Available</h4>
                    <p className="text-gray-600 text-sm">Most repairs and services can be completed while you wait.</p>
                  </div>
                </div>
                <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                  <Check className="h-6 w-6 mt-1 flex-shrink-0" style={{ color: primaryColor }} />
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1">Family Owned & Operated</h4>
                    <p className="text-gray-600 text-sm">Local business committed to our Portland community.</p>
                  </div>
                </div>
                <div className="flex items-start gap-4 p-4 bg-gray-50 rounded-lg">
                  <Check className="h-6 w-6 mt-1 flex-shrink-0" style={{ color: primaryColor }} />
                  <div>
                    <h4 className="font-semibold text-gray-800 mb-1">Comprehensive Auto Care</h4>
                    <p className="text-gray-600 text-sm">From routine maintenance to complex repairs, we handle it all.</p>
                  </div>
                </div>
              </div>
            </div>

            <Card className="p-6 border-l-4" style={{ borderLeftColor: primaryColor }}>
              <CardContent className="p-0">
                <h4 className="text-xl font-bold mb-3" style={{ color: primaryColor }}>Our Promise</h4>
                <p className="text-gray-600 leading-relaxed">
                  We guarantee quality workmanship and stand behind every service with our comprehensive warranty. Your satisfaction and safety are our top priorities.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresAbout;
