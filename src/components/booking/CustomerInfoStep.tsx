
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
  t: any; // Accept translations from parent
}

export const CustomerInfoStep: React.FC<CustomerInfoStepProps> = ({
  customerInfo,
  onInputChange,
  onNext,
  t
}) => {
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    // Basic validation
    if (!customerInfo.firstName || !customerInfo.lastName || !customerInfo.phone || 
        !customerInfo.service || !customerInfo.preferredDate) {
      alert(t.booking.validationRequired);
      return;
    }

    // Additional validation for service-specific fields
    if (isTireService && !customerInfo.tireSize) {
      alert(t.booking.validationTireSize);
      return;
    }

    if (requiresVehicleInfo && !customerInfo.licensePlate && !customerInfo.vin) {
      alert(t.booking.validationVehicleId);
      return;
    }

    if (requiresAddress && (!customerInfo.address || !customerInfo.city || !customerInfo.state || !customerInfo.zip)) {
      const serviceName = isMobileService ? t.booking.mobileServiceText : t.booking.roadsideAssistanceText;
      alert(`${t.booking.validationAddress} ${serviceName}`);
      return;
    }

    onNext();
  };

  const services = [
    { value: 'new-tires', label: t.booking.newTires },
    { value: 'used-tires', label: t.booking.usedTires },
    { value: 'mount-and-balance-tires', label: t.booking.mountAndBalanceTires },
    { value: 'tire-repair', label: t.booking.tireRepair },
    { value: 'oil-change', label: t.booking.oilChange },
    { value: 'front-or-back-brake-change', label: t.booking.frontOrBackBrakeChange },
    { value: 'full-brake-change', label: t.booking.fullBrakeChange },
    { value: 'tuneup', label: t.booking.tuneup },
    { value: 'alignment', label: t.booking.alignment },
    { value: 'mechanical-inspection-and-estimate', label: t.booking.mechanicalInspectionAndEstimate },
    { value: 'mobile-service', label: t.booking.mobileService },
    { value: 'roadside-assistance', label: t.booking.roadsideAssistance }
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
            {t.booking.personalInfo}
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-2">
                {t.booking.firstName} *
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
                {t.booking.lastName} *
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
                {t.booking.phoneNumber} *
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
                {t.booking.emailAddress}
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
            {t.booking.serviceInfo}
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-2">
              {t.booking.serviceNeeded} *
            </label>
            <select
              value={customerInfo.service}
              onChange={(e) => onInputChange('service', e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
              required
            >
              <option value="">{t.booking.selectService}</option>
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
                    <p className="font-medium text-yellow-800">{t.booking.newTiresPricing}</p>
                    <p className="text-sm text-yellow-700">
                      {t.booking.newTiresPricingDesc}{' '}
                      <a href="tel:503-367-9714" className="font-medium underline hover:text-yellow-800">
                        (503) 367-9714
                      </a>{' '}
                      {t.booking.newTiresPricingDesc2}
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
                {t.booking.tireSize} *
              </label>
              <div className="mb-4">
                <img 
                  src="/lovable-uploads/76982728-b5a9-4195-af0f-d91ebb846545.png" 
                  alt="Tire Size Reference Guide" 
                  className="w-full max-w-md mx-auto rounded-lg border border-gray-300"
                />
                <p className="text-sm text-gray-600 mt-2 text-center">
                  {t.booking.tireSizeRef}
                </p>
              </div>
              <input
                type="text"
                value={customerInfo.tireSize}
                onChange={(e) => onInputChange('tireSize', e.target.value)}
                placeholder={t.booking.tireSizePlaceholder}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                required
              />
            </div>
          )}

          {/* Vehicle Information */}
          {(requiresVehicleInfo || isTireService) && (
            <Card className="border-gray-200">
              <CardHeader>
                <CardTitle className="text-sm">{t.booking.vehicleInfo}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      {t.booking.make}
                    </label>
                    <input
                      type="text"
                      value={customerInfo.vehicleMake}
                      onChange={(e) => onInputChange('vehicleMake', e.target.value)}
                      placeholder={t.booking.makePlaceholder}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      {t.booking.model}
                    </label>
                    <input
                      type="text"
                      value={customerInfo.vehicleModel}
                      onChange={(e) => onInputChange('vehicleModel', e.target.value)}
                      placeholder={t.booking.modelPlaceholder}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      {t.booking.year}
                    </label>
                    <input
                      type="number"
                      value={customerInfo.vehicleYear}
                      onChange={(e) => onInputChange('vehicleYear', e.target.value)}
                      placeholder={t.booking.yearPlaceholder}
                      min="1900"
                      max={new Date().getFullYear() + 1}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    />
                  </div>
                </div>
                
                {requiresVehicleInfo && (
                  <div>
                    <p className="text-sm font-medium text-gray-700 mb-2">
                      {t.booking.vehicleIdentification} *
                    </p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium mb-2">
                          {t.booking.licensePlate}
                        </label>
                        <input
                          type="text"
                          value={customerInfo.licensePlate}
                          onChange={(e) => onInputChange('licensePlate', e.target.value)}
                          placeholder={t.booking.licensePlatePlaceholder}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium mb-2">
                          {t.booking.vin}
                        </label>
                        <input
                          type="text"
                          value={customerInfo.vin}
                          onChange={(e) => onInputChange('vin', e.target.value)}
                          placeholder={t.booking.vinPlaceholder}
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
                  {isMobileService ? t.booking.serviceAddress : t.booking.locationAddress}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <label className="block text-sm font-medium mb-2">
                    {t.booking.streetAddress} *
                  </label>
                  <input
                    type="text"
                    value={customerInfo.address}
                    onChange={(e) => onInputChange('address', e.target.value)}
                    placeholder={t.booking.addressPlaceholder}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                    required
                  />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      {t.booking.city} *
                    </label>
                    <input
                      type="text"
                      value={customerInfo.city}
                      onChange={(e) => onInputChange('city', e.target.value)}
                      placeholder={t.booking.cityPlaceholder}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      {t.booking.state} *
                    </label>
                    <input
                      type="text"
                      value={customerInfo.state}
                      onChange={(e) => onInputChange('state', e.target.value)}
                      placeholder={t.booking.statePlaceholder}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">
                      {t.booking.zipCode} *
                    </label>
                    <input
                      type="text"
                      value={customerInfo.zip}
                      onChange={(e) => onInputChange('zip', e.target.value)}
                      placeholder={t.booking.zipPlaceholder}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
                      required
                    />
                  </div>
                </div>
                <div className={`${isMobileService ? 'bg-blue-100' : 'bg-orange-100'} p-3 rounded-lg`}>
                  <p className={`text-sm ${isMobileService ? 'text-blue-800' : 'text-orange-800'}`}>
                    {isMobileService 
                      ? t.booking.mobileServiceNote
                      : t.booking.roadsideNote}
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
              {t.booking.preferredDate} *
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
            {t.booking.additionalInfo}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div>
            <label className="block text-sm font-medium mb-2">
              {t.booking.additionalMessage}
            </label>
            <textarea
              value={customerInfo.message}
              onChange={(e) => onInputChange('message', e.target.value)}
              rows={4}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007030]"
              placeholder={t.booking.vehiclePlaceholder}
            />
          </div>
        </CardContent>
      </Card>

      {/* Waiting Room Comfort Message */}
      <Card className="border-2 border-[#007030] bg-green-50">
        <CardContent className="p-4">
          <p className="text-[#007030] font-medium text-center">
            {t.booking.waitingRoomMessage}
          </p>
        </CardContent>
      </Card>

      <div className="flex justify-end">
        <Button 
          type="submit"
          className="bg-[#007030] hover:bg-[#005a26] text-white px-8 py-3"
        >
          {t.booking.nextButton}
        </Button>
      </div>
    </form>
  );
};
