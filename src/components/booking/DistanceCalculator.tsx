import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { MapPin, Calculator, Truck } from 'lucide-react';

interface DistanceCalculatorProps {
  address: string;
  city: string;
  state: string;
  zip: string;
  serviceType: 'mobile-service' | 'roadside-assistance';
  onDistanceCalculated?: (distance: number, cost: number) => void;
}

interface DistanceResult {
  distance: number;
  duration: string;
  cost: number;
}

export const DistanceCalculator: React.FC<DistanceCalculatorProps> = ({
  address,
  city,
  state,
  zip,
  serviceType,
  onDistanceCalculated
}) => {
  const [distanceResult, setDistanceResult] = useState<DistanceResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Oregon Tires location
  const shopAddress = "8536 SE 82nd Ave, Portland, OR 97266";

  const calculateDistance = async () => {
    if (!address || !city || !state || !zip) {
      setError("Please fill in complete address information");
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const customerAddress = `${address}, ${city}, ${state} ${zip}`;
      
      // Using a simple distance calculation for demo
      // In production, you'd want to use Google Maps API, MapBox, or similar
      const mockDistance = Math.floor(Math.random() * 25) + 5; // 5-30 miles
      const mockDuration = `${Math.floor(mockDistance * 2 + 10)}-${Math.floor(mockDistance * 2 + 20)} minutes`;
      
      // Calculate cost based on distance and service type
      let baseCost = serviceType === 'roadside-assistance' ? 75 : 50;
      let costPerMile = serviceType === 'roadside-assistance' ? 3 : 2;
      let totalCost = baseCost + (mockDistance * costPerMile);

      setDistanceResult({
        distance: mockDistance,
        duration: mockDuration,
        cost: totalCost
      });

      // Call the callback to update parent component
      if (onDistanceCalculated) {
        onDistanceCalculated(mockDistance, totalCost);
      }
    } catch (err) {
      setError("Unable to calculate distance. Please check your address.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (address && city && state && zip) {
      const timeoutId = setTimeout(() => {
        calculateDistance();
      }, 1000); // Debounce the calculation

      return () => clearTimeout(timeoutId);
    }
  }, [address, city, state, zip]);

  const getServiceIcon = () => {
    return serviceType === 'roadside-assistance' ? <Truck className="h-5 w-5" /> : <MapPin className="h-5 w-5" />;
  };

  const getServiceTitle = () => {
    return serviceType === 'roadside-assistance' ? 'Roadside Assistance Calculator' : 'Mobile Service Calculator';
  };

  return (
    <Card className="mt-4">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg">
          {getServiceIcon()}
          {getServiceTitle()}
        </CardTitle>
      </CardHeader>
      <CardContent>
        {!address || !city || !state || !zip ? (
          <p className="text-gray-500 text-sm">
            Complete your address information to calculate distance and pricing
          </p>
        ) : loading ? (
          <div className="flex items-center gap-2 text-blue-600">
            <Calculator className="h-4 w-4 animate-spin" />
            <span>Calculating distance...</span>
          </div>
        ) : error ? (
          <div className="text-red-600 text-sm">
            <p>{error}</p>
            <Button 
              type="button" 
              variant="outline" 
              size="sm" 
              onClick={calculateDistance}
              className="mt-2"
            >
              Try Again
            </Button>
          </div>
        ) : distanceResult ? (
          <div className="space-y-3">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="font-semibold text-gray-700">Distance</p>
                <p className="text-xl font-bold text-[#007030]">{distanceResult.distance} miles</p>
              </div>
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="font-semibold text-gray-700">Travel Time</p>
                <p className="text-xl font-bold text-[#007030]">{distanceResult.duration}</p>
              </div>
              <div className="bg-gray-50 p-3 rounded-lg">
                <p className="font-semibold text-gray-700">Service Cost</p>
                <p className="text-xl font-bold text-[#007030]">${distanceResult.cost}</p>
              </div>
            </div>
            
            <div className="text-xs text-gray-600 space-y-1">
              <p>• Distance calculated from Oregon Tires to your location</p>
              <p>• {serviceType === 'roadside-assistance' 
                ? 'Roadside assistance includes emergency service call fee' 
                : 'Mobile service brings our shop to your home'}</p>
              <p>• Final pricing may vary based on specific service requirements</p>
            </div>
          </div>
        ) : null}
      </CardContent>
    </Card>
  );
};