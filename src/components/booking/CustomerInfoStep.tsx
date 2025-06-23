
import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CustomerInfo } from '@/pages/AppointmentBooking';

interface CustomerInfoStepProps {
  onNext: (info: CustomerInfo) => void;
  initialData: CustomerInfo;
}

export const CustomerInfoStep: React.FC<CustomerInfoStepProps> = ({ onNext, initialData }) => {
  const [formData, setFormData] = useState<CustomerInfo>(initialData);
  const [errors, setErrors] = useState<Partial<CustomerInfo>>({});

  const validateForm = () => {
    const newErrors: Partial<CustomerInfo> = {};
    
    if (!formData.firstName.trim()) newErrors.firstName = 'First name is required';
    if (!formData.lastName.trim()) newErrors.lastName = 'Last name is required';
    if (!formData.email.trim()) newErrors.email = 'Email is required';
    if (!formData.phone.trim()) newErrors.phone = 'Phone is required';
    if (!formData.service) newErrors.service = 'Service selection is required';
    if (!formData.preferredDate) newErrors.preferredDate = 'Date is required';
    
    // Email validation
    if (formData.email && !/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm()) {
      onNext(formData);
    }
  };

  const updateFormData = (field: keyof CustomerInfo, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    // Clear error when user starts typing
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: undefined }));
    }
  };

  // Set minimum date to today
  const today = new Date().toISOString().split('T')[0];

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div className="grid md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="firstName">First Name *</Label>
          <Input
            id="firstName"
            value={formData.firstName}
            onChange={(e) => updateFormData('firstName', e.target.value)}
            className={errors.firstName ? 'border-red-500' : ''}
          />
          {errors.firstName && <p className="text-red-500 text-sm mt-1">{errors.firstName}</p>}
        </div>
        
        <div>
          <Label htmlFor="lastName">Last Name *</Label>
          <Input
            id="lastName"
            value={formData.lastName}
            onChange={(e) => updateFormData('lastName', e.target.value)}
            className={errors.lastName ? 'border-red-500' : ''}
          />
          {errors.lastName && <p className="text-red-500 text-sm mt-1">{errors.lastName}</p>}
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="email">Email *</Label>
          <Input
            id="email"
            type="email"
            value={formData.email}
            onChange={(e) => updateFormData('email', e.target.value)}
            className={errors.email ? 'border-red-500' : ''}
          />
          {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
        </div>
        
        <div>
          <Label htmlFor="phone">Phone *</Label>
          <Input
            id="phone"
            type="tel"
            value={formData.phone}
            onChange={(e) => updateFormData('phone', e.target.value)}
            className={errors.phone ? 'border-red-500' : ''}
          />
          {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone}</p>}
        </div>
      </div>

      <div>
        <Label htmlFor="service">Service Type *</Label>
        <Select value={formData.service} onValueChange={(value) => updateFormData('service', value)}>
          <SelectTrigger className={errors.service ? 'border-red-500' : ''}>
            <SelectValue placeholder="Select a service" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="tire-installation">Tire Installation (1.5h)</SelectItem>
            <SelectItem value="tire-repair">Tire Repair (1.5h)</SelectItem>
            <SelectItem value="wheel-alignment">Wheel Alignment (1.5h)</SelectItem>
            <SelectItem value="brake-service">Brake Service (2.5h)</SelectItem>
            <SelectItem value="brake-repair">Brake Repair (2.5h)</SelectItem>
            <SelectItem value="oil-change">Oil Change (3.5h)</SelectItem>
            <SelectItem value="general-maintenance">General Maintenance (3.5h)</SelectItem>
            <SelectItem value="diagnostic">Diagnostic Service (3.5h)</SelectItem>
          </SelectContent>
        </Select>
        {errors.service && <p className="text-red-500 text-sm mt-1">{errors.service}</p>}
      </div>

      <div>
        <Label htmlFor="preferredDate">Preferred Date *</Label>
        <Input
          id="preferredDate"
          type="date"
          min={today}
          value={formData.preferredDate}
          onChange={(e) => updateFormData('preferredDate', e.target.value)}
          className={errors.preferredDate ? 'border-red-500' : ''}
        />
        {errors.preferredDate && <p className="text-red-500 text-sm mt-1">{errors.preferredDate}</p>}
      </div>

      <div className="flex justify-end pt-4">
        <Button type="submit" className="bg-[#007030] hover:bg-[#005a26] px-8">
          Next: Review Schedule
        </Button>
      </div>
    </form>
  );
};
