
import React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CustomerInfo } from '@/pages/AppointmentBooking';

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
  const isFormValid = 
    customerInfo.firstName.trim() !== '' &&
    customerInfo.lastName.trim() !== '' &&
    customerInfo.email.trim() !== '' &&
    customerInfo.phone.trim() !== '' &&
    customerInfo.service !== '' &&
    customerInfo.preferredDate !== '';

  return (
    <div className="max-w-2xl mx-auto">
      <h2 className="text-2xl font-bold text-[#0C3B1B] mb-6">Customer Information</h2>
      
      <div className="space-y-6">
        <div className="grid md:grid-cols-2 gap-4">
          <div>
            <Label htmlFor="firstName">First Name *</Label>
            <Input
              id="firstName"
              type="text"
              value={customerInfo.firstName}
              onChange={(e) => onInputChange('firstName', e.target.value)}
              required
            />
          </div>
          <div>
            <Label htmlFor="lastName">Last Name *</Label>
            <Input
              id="lastName"
              type="text"
              value={customerInfo.lastName}
              onChange={(e) => onInputChange('lastName', e.target.value)}
              required
            />
          </div>
        </div>

        <div className="grid md:grid-cols-2 gap-4">
          <div>
            <Label htmlFor="email">Email *</Label>
            <Input
              id="email"
              type="email"
              value={customerInfo.email}
              onChange={(e) => onInputChange('email', e.target.value)}
              required
            />
          </div>
          <div>
            <Label htmlFor="phone">Phone *</Label>
            <Input
              id="phone"
              type="tel"
              value={customerInfo.phone}
              onChange={(e) => onInputChange('phone', e.target.value)}
              required
            />
          </div>
        </div>

        <div>
          <Label htmlFor="service">Service Needed *</Label>
          <Select
            value={customerInfo.service}
            onValueChange={(value) => onInputChange('service', value)}
          >
            <SelectTrigger>
              <SelectValue placeholder="Select a service" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="new-or-used-tires">New or Used Tires (2 hours)</SelectItem>
              <SelectItem value="mount-and-balance-tires">Mount and Balance Tires (2 hours)</SelectItem>
              <SelectItem value="tire-repair">Tire Repair (1 hour)</SelectItem>
              <SelectItem value="oil-change">Oil Change (1.25 hours)</SelectItem>
              <SelectItem value="front-or-back-brake-change">Front or Back Brake Change (2 hours)</SelectItem>
              <SelectItem value="full-brake-change">Full Brake Change (3.5 hours)</SelectItem>
              <SelectItem value="tuneup">Tuneup (5 hours)</SelectItem>
              <SelectItem value="alignment">Alignment (2 hours)</SelectItem>
              <SelectItem value="mechanical-inspection-and-estimate">Mechanical Inspection and Estimate (2.5 hours)</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div>
          <Label htmlFor="preferredDate">Preferred Date *</Label>
          <Input
            id="preferredDate"
            type="date"
            value={customerInfo.preferredDate}
            onChange={(e) => onInputChange('preferredDate', e.target.value)}
            required
          />
        </div>

        <div>
          <Label htmlFor="message">Additional Message</Label>
          <textarea
            id="message"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#0C3B1B] focus:border-transparent"
            rows={4}
            value={customerInfo.message}
            onChange={(e) => onInputChange('message', e.target.value)}
            placeholder="Any additional details about your service needs..."
          />
        </div>

        <Button
          onClick={onNext}
          disabled={!isFormValid}
          className="w-full bg-[#0C3B1B] hover:bg-[#083018] text-white"
        >
          Continue to Schedule Review
        </Button>
      </div>
    </div>
  );
};
