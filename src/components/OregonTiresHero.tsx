
import { useLanguage } from '@/hooks/useLanguage';
import { Button } from '@/components/ui/button';
import { Calendar, Phone } from 'lucide-react';

export const OregonTiresHero = () => {
  const { language } = useLanguage();

  const scrollToContact = () => {
    const contactSection = document.getElementById('contact');
    if (contactSection) {
      contactSection.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const openAvailability = () => {
    window.open('/availability', '_blank');
  };

  return (
    <section className="relative py-20 px-4 bg-gradient-to-br from-white to-green-50">
      <div className="container mx-auto text-center">
        <h1 className="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
          Oregon Tires - Professional Tire Services
        </h1>
        <p className="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
          Expert tire installation, repair, and maintenance services in Portland, Oregon
        </p>
        
        <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <Button 
            onClick={scrollToContact}
            className="bg-[#007030] hover:bg-[#005825] text-white px-8 py-3 text-lg flex items-center gap-2"
          >
            <Phone className="h-5 w-5" />
            Contact Us
          </Button>
          
          <Button 
            onClick={openAvailability}
            variant="outline"
            className="border-[#007030] text-[#007030] hover:bg-[#007030] hover:text-white px-8 py-3 text-lg flex items-center gap-2"
          >
            <Calendar className="h-5 w-5" />
            Check Availability
          </Button>
        </div>

        <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
          <div className="text-center">
            <div className="bg-[#FEE11A] w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-gray-900">15+</span>
            </div>
            <h3 className="font-semibold text-gray-900 mb-2">Years Experience</h3>
            <p className="text-gray-600">Serving Portland since 2008</p>
          </div>
          
          <div className="text-center">
            <div className="bg-[#FEE11A] w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-gray-900">2</span>
            </div>
            <h3 className="font-semibold text-gray-900 mb-2">Languages</h3>
            <p className="text-gray-600">English & Spanish</p>
          </div>
          
          <div className="text-center">
            <div className="bg-[#FEE11A] w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-gray-900">★</span>
            </div>
            <h3 className="font-semibold text-gray-900 mb-2">Quality Service</h3>
            <p className="text-gray-600">Honest & reliable work</p>
          </div>
        </div>
      </div>
    </section>
  );
};

export default OregonTiresHero;
