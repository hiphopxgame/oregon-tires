
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CustomerInfo } from '@/pages/AppointmentBooking';
import { CheckCircle } from 'lucide-react';

interface BookingSummaryProps {
  customerInfo: CustomerInfo;
  serviceDuration: number;
}

export const BookingSummary: React.FC<BookingSummaryProps> = ({ customerInfo, serviceDuration }) => {
  const selectedDate = new Date(customerInfo.preferredDate + 'T00:00:00');

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <CheckCircle className="h-5 w-5 text-green-600" />
          Booking Summary
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid md:grid-cols-2 gap-4 text-sm">
          <div>
            <p><strong>Customer:</strong> {customerInfo.firstName} {customerInfo.lastName}</p>
            <p><strong>Email:</strong> {customerInfo.email}</p>
            <p><strong>Phone:</strong> {customerInfo.phone}</p>
          </div>
          <div>
            <p><strong>Service:</strong> {customerInfo.service.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
            <p><strong>Date:</strong> {selectedDate.toLocaleDateString()}</p>
            <p><strong>Duration:</strong> {serviceDuration} hours</p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};
