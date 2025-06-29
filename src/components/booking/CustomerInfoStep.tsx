
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { User, Calendar, MessageSquare, Wrench } from 'lucide-react';

interface CustomerInfoStepProps {
  customerInfo: CustomerInfo;
  onInputChange: (field: keyof CustomerInfo, value: string) => void;
  onNext: () => void;
}

export const CustomerInfoStep: React.FC<CustomerInfoStepProps> = ({
  customerInfo,
  onInputChange,
  onNext
}) => {
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Basic validation
    if (!customerInfo.firstName || !customerInfo.lastName || !customerInfo.phone || 
        !customerInfo.service || !customerInfo.preferredDate) {
      alert('Please fill in all required fields');
      return;
    }

    onNext();
  };

  const services = [
    { value: 'new-tires', label: 'New Tires' },
    { value: 'used-tires', label: 'Used Tires' },
    { value: 'mount-and-balance-tires', label: 'Mount and Balance Tires' },
    { value: 'tire-repair', label: 'Tire Repair' },
    { value: 'oil-change', label: 'Oil Change' },
    { value: 'front-or-back-brake-change', label: 'Front or Back Brake Change' },
    { value: 'full-brake-change', label: 'Full Brake Change' },
    { value: 'tuneup', label: 'Tuneup' },
    { value: 'alignment', label: 'Alignment' },
    { value: 'mechanical-inspection-and-estimate', label: 'Mechanical Inspection and Estimate' }
  ];

  // Get today's date for minimum date selection
  const today = new Date().toISOString().split('T')[0];

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Personal Information
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-2">
                First Name *
              </label>
              <input
                type="text"
                value={customerInfo.firstName}
                onChange={(e) => onInputChange('firstName', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-2">
                Last Name *
              </label>
              <input
                type="text"
                value={customerInfo.lastName}
                onChange={(e) => onInputChange('lastName', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                required
              />
            </div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-2">
                Phone Number *
              </label>
              <input
                type="tel"
                value={customerInfo.phone}
                onChange={(e) => onInputChange('phone', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-2">
                Email Address
              </label>
              <input
                type="email"
                value={customerInfo.email}
                onChange={(e) => onInputChange('email', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Wrench className="h-5 w-5" />
            Service Information
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-2">
              Service Needed *
            </label>
            <select
              value={customerInfo.service}
              onChange={(e) => onInputChange('service', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
              required
            >
              <option value="">Select a service</option>
              {services.map((service) => (
                <option key={service.value} value={service.value}>
                  {service.label}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">
              Preferred Date *
            </label>
            <input
              type="date"
              value={customerInfo.preferredDate}
              onChange={(e) => onInputChange('preferredDate', e.target.value)}
              min={today}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
              required
            />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <MessageSquare className="h-5 w-5" />
            Additional Information
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div>
            <label className="block text-sm font-medium mb-2">
              Message (Optional)
            </label>
            <textarea
              value={customerInfo.message}
              onChange={(e) => onInputChange('message', e.target.value)}
              rows={4}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
              placeholder="Please describe any specific concerns or additional information about your vehicle..."
            />
          </div>
        </CardContent>
      </Card>

      {/* Waiting Room Comfort Message */}
      <Card className="border-2 border-[#007030] bg-green-50">
        <CardContent className="p-4">
          <p className="text-[#007030] font-medium text-center">
            While receiving your high quality auto service, please enjoy the comfort of our waiting room with free coffee, energy drinks, and more!
          </p>
        </CardContent>
      </Card>

      <div className="flex justify-end">
        <Button 
          type="submit"
          className="bg-[#007030] hover:bg-[#005a26] text-white px-8 py-3"
        >
          Continue to Schedule
        </Button>
      </div>
    </form>
  );
};
