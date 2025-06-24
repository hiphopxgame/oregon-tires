
import React, { useState } from 'react';
import { CustomerInfoStep } from '@/components/booking/CustomerInfoStep';
import { ScheduleViewStep } from '@/components/booking/ScheduleViewStep';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-react';

export interface CustomerInfo {
  firstName: string;
  lastName: string;
  phone: string;
  email: string;
  service: string;
  preferredDate: string;
  message: string;
}

const AppointmentBooking = () => {
  const [currentStep, setCurrentStep] = useState(1);
  const [customerInfo, setCustomerInfo] = useState<CustomerInfo>({
    firstName: '',
    lastName: '',
    phone: '',
    email: '',
    service: '',
    preferredDate: '',
    message: ''
  });

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
    <div className="min-h-screen bg-gray-50 py-8">
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
              <h1 className="text-3xl font-bold text-[#0C3B1B]">
                Book Your Appointment
              </h1>
            </div>
            
            {/* Step indicator */}
            <div className="flex items-center gap-4">
              <div className={`flex items-center gap-2 ${currentStep >= 1 ? 'text-[#0C3B1B]' : 'text-gray-400'}`}>
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                  currentStep >= 1 ? 'bg-[#0C3B1B] text-white' : 'bg-gray-200'
                }`}>
                  1
                </div>
                <span>Customer Info</span>
              </div>
              <div className={`w-12 h-0.5 ${currentStep >= 2 ? 'bg-[#0C3B1B]' : 'bg-gray-200'}`}></div>
              <div className={`flex items-center gap-2 ${currentStep >= 2 ? 'text-[#0C3B1B]' : 'text-gray-400'}`}>
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                  currentStep >= 2 ? 'bg-[#0C3B1B] text-white' : 'bg-gray-200'
                }`}>
                  2
                </div>
                <span>Schedule Review</span>
              </div>
            </div>
          </div>

          {/* Step content */}
          {currentStep === 1 && (
            <CustomerInfoStep 
              customerInfo={customerInfo} 
              onInputChange={handleInputChange}
              onNext={handleNext} 
            />
          )}
          
          {currentStep === 2 && (
            <ScheduleViewStep customerInfo={customerInfo} />
          )}
        </div>
      </div>
    </div>
  );
};

export default AppointmentBooking;
