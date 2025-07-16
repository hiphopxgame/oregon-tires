
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { User, Calendar, MessageSquare, Wrench, MapPin } from 'lucide-react';
import { DistanceCalculator } from './DistanceCalculator';

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

    // Additional validation for service-specific fields
    if (isTireService && !customerInfo.tireSize) {
      alert('Please enter your tire size for tire services');
      return;
    }

    if (requiresVehicleInfo && !customerInfo.licensePlate && !customerInfo.vin) {
      alert('Please enter either your License Plate Number or VIN for this service');
      return;
    }

    if (requiresAddress && (!customerInfo.address || !customerInfo.city || !customerInfo.state || !customerInfo.zip)) {
      const serviceName = isMobileService ? 'mobile service' : 'roadside assistance';
      alert(`Please fill in your complete address for ${serviceName}`);
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
    { value: 'mechanical-inspection-and-estimate', label: 'Mechanical Inspection and Estimate' },
    { value: 'mobile-service', label: 'Mobile Service (At Your Home)' },
    { value: 'roadside-assistance', label: 'Roadside Assistance (Any Location)' }
  ];

  // Tire services that require tire size
  const tireServices = ['new-tires', 'used-tires', 'mount-and-balance-tires', 'tire-repair'];
  
  // Services that require license plate or VIN
  const requiresVehicleInfo = !tireServices.includes(customerInfo.service) && customerInfo.service !== '' && customerInfo.service !== 'mobile-service' && customerInfo.service !== 'roadside-assistance';

  const isTireService = tireServices.includes(customerInfo.service);
  const isMobileService = customerInfo.service === 'mobile-service';
  const isRoadsideService = customerInfo.service === 'roadside-assistance';
  const requiresAddress = isMobileService || isRoadsideService;

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
            
            {/* New Tires Pricing Notice */}
            {customerInfo.service === 'new-tires' && (
              <div className="mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div className="flex items-center gap-2">
                  <span className="text-2xl">💰</span>
                  <div>
                    <p className="font-medium text-yellow-800">New Tires - Call for Pricing</p>
                    <p className="text-sm text-yellow-700">
                      Tire prices vary based on size, brand, and availability. Please call us at{' '}
                      <a href="tel:503-555-0123" className="font-medium underline hover:text-yellow-800">
                        (503) 555-0123
                      </a>{' '}
                      for current pricing and availability.
                    </p>
                  </div>
                </div>
              </div>
            )}
          </div>

          {/* Tire Size Field for Tire Services */}
          {isTireService && (
            <div>
              <label className="block text-sm font-medium mb-2">
                Tire Size *
              </label>
              <div className="mb-4">
                <img 
                  src="/lovable-uploads/76982728-b5a9-4195-af0f-d91ebb846545.png" 
                  alt="Tire Size Reference Guide" 
                  className="w-full max-w-md mx-auto rounded-lg border border-gray-300"
                />
                <p className="text-sm text-gray-600 mt-2 text-center">
                  Use the reference above to find your tire size (e.g., 195/55R16)
                </p>
              </div>
              <input
                type="text"
                value={customerInfo.tireSize}
                onChange={(e) => onInputChange('tireSize', e.target.value)}
                placeholder="e.g., 195/55R16"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                required
              />
            </div>
          )}

          {/* Vehicle Information */}
          {(requiresVehicleInfo || isTireService) && (
            <Card className="border-gray-200">
              <CardHeader>
                <CardTitle className="text-sm">Vehicle Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      Make
                    </label>
                    <input
                      type="text"
                      value={customerInfo.vehicleMake}
                      onChange={(e) => onInputChange('vehicleMake', e.target.value)}
                      placeholder="e.g., Toyota"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      Model
                    </label>
                    <input
                      type="text"
                      value={customerInfo.vehicleModel}
                      onChange={(e) => onInputChange('vehicleModel', e.target.value)}
                      placeholder="e.g., Camry"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      Year
                    </label>
                    <input
                      type="number"
                      value={customerInfo.vehicleYear}
                      onChange={(e) => onInputChange('vehicleYear', e.target.value)}
                      placeholder="e.g., 2020"
                      min="1900"
                      max={new Date().getFullYear() + 1}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    />
                  </div>
                </div>
                
                {requiresVehicleInfo && (
                  <div>
                    <p className="text-sm font-medium text-gray-700 mb-2">
                      Vehicle Identification (Please provide at least one) *
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium mb-2">
                          License Plate Number
                        </label>
                        <input
                          type="text"
                          value={customerInfo.licensePlate}
                          onChange={(e) => onInputChange('licensePlate', e.target.value)}
                          placeholder="e.g., ABC123"
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium mb-2">
                          VIN (Vehicle Identification Number)
                        </label>
                        <input
                          type="text"
                          value={customerInfo.vin}
                          onChange={(e) => onInputChange('vin', e.target.value)}
                          placeholder="17-character VIN"
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                          maxLength={17}
                        />
                      </div>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>
          )}

          {/* Address Fields for Mobile Service and Roadside Assistance */}
          {requiresAddress && (
            <Card className={`border-2 ${isMobileService ? 'border-blue-200 bg-blue-50' : 'border-orange-200 bg-orange-50'}`}>
              <CardHeader>
                <CardTitle className={`flex items-center gap-2 ${isMobileService ? 'text-blue-800' : 'text-orange-800'}`}>
                  <MapPin className="h-5 w-5" />
                  {isMobileService ? 'Service Address (Required for Mobile Service)' : 'Location Address (Required for Roadside Assistance)'}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <label className="block text-sm font-medium mb-2">
                    Street Address *
                  </label>
                  <input
                    type="text"
                    value={customerInfo.address}
                    onChange={(e) => onInputChange('address', e.target.value)}
                    placeholder="e.g., 123 Main Street"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    required
                  />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      City *
                    </label>
                    <input
                      type="text"
                      value={customerInfo.city}
                      onChange={(e) => onInputChange('city', e.target.value)}
                      placeholder="e.g., Portland"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      State *
                    </label>
                    <input
                      type="text"
                      value={customerInfo.state}
                      onChange={(e) => onInputChange('state', e.target.value)}
                      placeholder="e.g., Oregon"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      ZIP Code *
                    </label>
                    <input
                      type="text"
                      value={customerInfo.zip}
                      onChange={(e) => onInputChange('zip', e.target.value)}
                      placeholder="e.g., 97201"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                      required
                    />
                  </div>
                </div>
                <div className={`${isMobileService ? 'bg-blue-100' : 'bg-orange-100'} p-3 rounded-lg`}>
                  <p className={`text-sm ${isMobileService ? 'text-blue-800' : 'text-orange-800'}`}>
                    {isMobileService 
                      ? '📍 Mobile service available within 25 miles of Portland. Additional travel charges may apply for longer distances.'
                      : '🚛 Emergency roadside assistance available 24/7. Distance and urgency fees apply.'}
                  </p>
                </div>
                
                 {/* Distance Calculator */}
                <DistanceCalculator
                  address={customerInfo.address}
                  city={customerInfo.city}
                  state={customerInfo.state}
                  zip={customerInfo.zip}
                  serviceType={isMobileService ? 'mobile-service' : 'roadside-assistance'}
                  onDistanceCalculated={(distance, cost) => {
                    onInputChange('travel_distance_miles', distance.toString());
                    onInputChange('travel_cost_estimate', cost.toString());
                  }}
                />
              </CardContent>
            </Card>
          )}

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
