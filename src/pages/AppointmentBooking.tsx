
import React, { useState } from 'react';
import { CustomerInfoStep } from '@/components/booking/CustomerInfoStep';
import { ScheduleViewStep } from '@/components/booking/ScheduleViewStep';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Home, Phone, User, Calendar } from 'lucide-react';
import { Link } from 'react-router-dom';

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
    <div className="min-h-screen bg-gray-50">
      {/* Header with Navigation */}
      <header className="bg-[#007030] text-white shadow-lg">
        <div className="container mx-auto px-4 py-4">
          <div className="flex justify-between items-center">
            <div>
              <Link to="/" className="hover:opacity-80">
                <h1 className="text-2xl font-bold">Oregon Tires Auto Care</h1>
                <p className="text-white/80">Professional Tire & Auto Services</p>
              </Link>
            </div>
            <Link 
              to="/" 
              className="text-white hover:text-yellow-200 flex items-center gap-2"
            >
              <Home className="h-4 w-4" />
              Back to Home
            </Link>
          </div>
        </div>
      </header>

      {/* Navigation Bar */}
      <nav className="bg-white shadow-md">
        <div className="container mx-auto px-4">
          <div className="flex justify-center space-x-8 py-4">
            <a 
              href="/#services" 
              className="text-[#007030] hover:text-green-700 font-medium flex items-center gap-2"
            >
              Services
            </a>
            <a 
              href="/#about" 
              className="text-[#007030] hover:text-green-700 font-medium flex items-center gap-2"
            >
              About
            </a>
            <a 
              href="/#testimonials" 
              className="text-[#007030] hover:text-green-700 font-medium flex items-center gap-2"
            >
              Reviews
            </a>
            <a 
              href="/#contact" 
              className="text-[#007030] hover:text-green-700 font-medium flex items-center gap-2"
            >
              <Phone className="h-4 w-4" />
              Contact
            </a>
            <span className="text-[#007030] font-medium flex items-center gap-2 border-b-2 border-[#007030]">
              <Calendar className="h-4 w-4" />
              Schedule
            </span>
          </div>
        </div>
      </nav>

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
                  Book Your Appointment
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
                  <span>Customer Info</span>
                </div>
                <div className={`w-12 h-0.5 ${currentStep >= 2 ? 'bg-[#007030]' : 'bg-gray-200'}`}></div>
                <div className={`flex items-center gap-2 ${currentStep >= 2 ? 'text-[#007030]' : 'text-gray-400'}`}>
                  <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold ${
                    currentStep >= 2 ? 'bg-[#007030] text-white' : 'bg-gray-200'
                  }`}>
                    <Calendar className="h-4 w-4" />
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
    </div>
  );
};

export default AppointmentBooking;
