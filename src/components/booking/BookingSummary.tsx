
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { CheckCircle } from 'lucide-react';
import { useLanguage } from '@/hooks/useLanguage';

interface BookingSummaryProps {
  customerInfo: CustomerInfo;
  serviceDuration: number;
}

export const BookingSummary: React.FC<BookingSummaryProps> = ({ customerInfo, serviceDuration }) => {
  const { t } = useLanguage();
  
  // Get the proper translated service name
  const getServiceDisplayName = (serviceValue: string) => {
    const serviceMap: { [key: string]: string } = {
      'new-tires': t.booking.newTires,
      'used-tires': t.booking.usedTires,
      'mount-and-balance-tires': t.booking.mountAndBalanceTires,
      'tire-repair': t.booking.tireRepair,
      'oil-change': t.booking.oilChange,
      'front-or-back-brake-change': t.booking.frontOrBackBrakeChange,
      'full-brake-change': t.booking.fullBrakeChange,
      'tuneup': t.booking.tuneup,
      'alignment': t.booking.alignment,
      'mechanical-inspection-and-estimate': t.booking.mechanicalInspectionAndEstimate,
      'mobile-service': t.booking.mobileService,
      'roadside-assistance': t.booking.roadsideAssistance
    };
    return serviceMap[serviceValue] || serviceValue.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
  };
  
  const selectedDate = new Date(customerInfo.preferredDate + 'T00:00:00');

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <CheckCircle className="h-5 w-5 text-green-600" />
          {t.booking.bookingSummary}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid md:grid-cols-2 gap-4 text-sm">
          <div>
            <p><strong>{t.booking.customer}:</strong> {customerInfo.firstName} {customerInfo.lastName}</p>
            <p><strong>{t.booking.email}:</strong> {customerInfo.email}</p>
            <p><strong>{t.booking.phone}:</strong> {customerInfo.phone}</p>
          </div>
          <div>
            <p><strong>{t.booking.service}:</strong> {getServiceDisplayName(customerInfo.service)}</p>
            <p><strong>{t.booking.date}:</strong> {selectedDate.toLocaleDateString()}</p>
            <p><strong>{t.booking.duration}:</strong> {serviceDuration} {t.booking.hours}</p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};
