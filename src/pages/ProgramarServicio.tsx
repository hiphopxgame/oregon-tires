import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { ArrowLeft, User, Calendar, Globe, Home } from 'lucide-react';
import { useLanguage } from '@/hooks/useLanguage';
import { CustomerInfoStep } from '@/components/booking/CustomerInfoStep';
import { ScheduleViewStep } from '@/components/booking/ScheduleViewStep';

export interface CustomerInfo {
  firstName: string;
  lastName: string;
  phone: string;
  email: string;
  service: string;
  preferredDate: string;
  message: string;
  tireSize: string;
  vehicleMake: string;
  vehicleModel: string;
  vehicleYear: string;
  licensePlate: string;
  vin: string;
  address: string;
  city: string;
  state: string;
  zip: string;
  travel_distance_miles: string;
  travel_cost_estimate: string;
}

const ProgramarServicio = () => {
  const { language, t, toggleLanguage } = useLanguage();
  const navigate = useNavigate();
  const [currentStep, setCurrentStep] = useState(1);
  const [customerInfo, setCustomerInfo] = useState<CustomerInfo>({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    service: '',
    preferredDate: '',
    message: '',
    tireSize: '',
    vehicleMake: '',
    vehicleModel: '',
    vehicleYear: '',
    licensePlate: '',
    vin: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    travel_distance_miles: '',
    travel_cost_estimate: ''
  });

  // Force Spanish language for this page
  React.useEffect(() => {
    if (language === 'english') {
      toggleLanguage();
    }
  }, [language, toggleLanguage]);

  const handleInputChange = (field: keyof CustomerInfo, value: string) => {
    setCustomerInfo(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleNext = () => {
    setCurrentStep(2);
  };

  const handleBack = () => {
    setCurrentStep(1);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header with Navigation */}
      <header className="bg-[#007030] text-white shadow-lg">
        <div className="container mx-auto px-4 py-4">
          <div className="flex justify-between items-center">
            <Link to="/" className="hover:opacity-80 transition-opacity">
              <img 
                src="/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png" 
                alt="Oregon Tires Auto Care - Spanish & English Speaking" 
                className="h-12 w-auto"
              />
            </Link>
            <div className="flex items-center gap-4">
              {/* Language Toggle */}
              <Button
                variant="ghost"
                onClick={() => navigate('/book-appointment')}
                className="text-white hover:text-yellow-200 hover:bg-white/10 flex items-center gap-2"
              >
                <Globe className="h-4 w-4" />
                English
              </Button>
              {/* Home Link */}
              <Link 
                to="/" 
                className="text-white hover:text-yellow-200 flex items-center gap-2"
              >
                <Home className="h-4 w-4" />
                {t.booking.backToHome}
              </Link>
            </div>
          </div>
        </div>
      </header>

      <div className="py-8">
        <div className="container mx-auto px-4 max-w-4xl">
          <div className="bg-white rounded-lg shadow-lg p-6">
            {/* Header */}
            <div className="mb-8">
              <div className="flex items-center gap-4 mb-4">
                {currentStep === 2 && (
                  <Button variant="ghost" onClick={handleBack} className="p-2">
                    <ArrowLeft className="h-4 w-4" />
                  </Button>
                )}
                <h1 className="text-3xl font-bold text-[#007030]">
                  {t.booking.pageTitle}
                </h1>
              </div>
              
              {/* Step indicator */}
              <div className="flex items-center gap-4">
                <div className={`flex items-center gap-2 ${currentStep >= 1 ? 'text-[#007030]' : 'text-gray-400'}`}>
                  <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                    currentStep >= 1 ? 'bg-[#007030] text-white' : 'bg-gray-200'
                  }`}>
                    <User className="h-4 w-4" />
                  </div>
                  <span>{t.booking.customerInfo}</span>
                </div>
                <div className={`w-12 h-0.5 ${currentStep >= 2 ? 'bg-[#007030]' : 'bg-gray-200'}`}></div>
                <div className={`flex items-center gap-2 ${currentStep >= 2 ? 'text-[#007030]' : 'text-gray-400'}`}>
                  <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                    currentStep >= 2 ? 'bg-[#007030] text-white' : 'bg-gray-200'
                  }`}>
                    <Calendar className="h-4 w-4" />
                  </div>
                  <span>{t.booking.scheduleReview}</span>
                </div>
              </div>
            </div>

            {/* Step content */}
            {currentStep === 1 && (
              <CustomerInfoStep 
                customerInfo={customerInfo}
                onInputChange={handleInputChange}
                onNext={handleNext}
                t={t}
              />
            )}
            
            {currentStep === 2 && (
              <ScheduleViewStep customerInfo={customerInfo} t={t} />
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProgramarServicio;