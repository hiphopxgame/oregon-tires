import React, { useState, useRef } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Upload, Save, RotateCcw } from 'lucide-react';
import { toast } from '@/components/ui/use-toast';

interface ServiceImage {
  id: string;
  title: string;
  imagePath: string;
  position: { x: number; y: number };
  scale: number;
}

const ServiceImagesManager = () => {
  const [images, setImages] = useState<ServiceImage[]>([
    { id: 'expert-technicians', title: 'Expert Technicians', imagePath: '/src/assets/expert-technicians.jpg', position: { x: 50, y: 50 }, scale: 1 },
    { id: 'fast-cars', title: 'Fast Service', imagePath: '/src/assets/fast-cars.jpg', position: { x: 50, y: 50 }, scale: 1 },
    { id: 'quality-parts', title: 'Quality Parts', imagePath: '/src/assets/quality-car-parts.jpg', position: { x: 50, y: 50 }, scale: 1 },
    { id: 'bilingual-support', title: 'Bilingual Support', imagePath: '/src/assets/bilingual-support.jpg', position: { x: 50, y: 50 }, scale: 1 },
    { id: 'tire-shop', title: 'Tire Services', imagePath: '/src/assets/tire-shop.jpg', position: { x: 50, y: 50 }, scale: 1 },
    { id: 'auto-repair', title: 'Auto Maintenance', imagePath: '/src/assets/auto-repair.jpg', position: { x: 50, y: 50 }, scale: 1 },
    { id: 'specialized-tools', title: 'Specialized Services', imagePath: '/src/assets/specialized-tools.jpg', position: { x: 50, y: 50 }, scale: 1 },
  ]);

  const fileInputRefs = useRef<{ [key: string]: HTMLInputElement | null }>({});

  const handleFileUpload = async (serviceId: string, file: File) => {
    try {
      // Create a URL for the uploaded file
      const fileUrl = URL.createObjectURL(file);
      
      setImages(prev => prev.map(img => 
        img.id === serviceId 
          ? { ...img, imagePath: fileUrl }
          : img
      ));

      toast({
        title: "Image uploaded successfully",
        description: "The service image has been updated.",
      });
    } catch (error) {
      toast({
        title: "Upload failed",
        description: "Failed to upload the image. Please try again.",
        variant: "destructive",
      });
    }
  };

  const updateImagePosition = (serviceId: string, x: number, y: number) => {
    setImages(prev => prev.map(img => 
      img.id === serviceId 
        ? { ...img, position: { x, y } }
        : img
    ));
  };

  const updateImageScale = (serviceId: string, scale: number) => {
    setImages(prev => prev.map(img => 
      img.id === serviceId 
        ? { ...img, scale }
        : img
    ));
  };

  const resetImagePosition = (serviceId: string) => {
    setImages(prev => prev.map(img => 
      img.id === serviceId 
        ? { ...img, position: { x: 50, y: 50 }, scale: 1 }
        : img
    ));
  };

  const saveChanges = async () => {
    // In a real implementation, this would save to the database or update the actual files
    toast({
      title: "Changes saved",
      description: "Service image settings have been saved successfully.",
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h2 className="text-2xl font-bold">Service Images Manager</h2>
        <Button onClick={saveChanges} className="flex items-center gap-2">
          <Save className="h-4 w-4" />
          Save All Changes
        </Button>
      </div>

      <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        {images.map((serviceImage) => (
          <Card key={serviceImage.id} className="overflow-hidden">
            <CardHeader className="pb-4">
              <CardTitle className="text-lg">{serviceImage.title}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Image Preview */}
              <div className="relative h-48 bg-gray-100 rounded-lg overflow-hidden">
                <div 
                  className="absolute inset-0 bg-cover transition-all duration-300"
                  style={{ 
                    backgroundImage: `url(${serviceImage.imagePath})`,
                    backgroundPosition: `${serviceImage.position.x}% ${serviceImage.position.y}%`,
                    transform: `scale(${serviceImage.scale})`,
                  }}
                />
                <div className="absolute inset-0 bg-black/30" />
                <div className="absolute bottom-4 left-4 right-4">
                  <div className="bg-white/95 backdrop-blur-sm rounded-lg p-3">
                    <h4 className="font-semibold text-gray-800">{serviceImage.title}</h4>
                    <p className="text-gray-600 text-sm">Sample description text</p>
                  </div>
                </div>
              </div>

              {/* Upload New Image */}
              <div className="space-y-2">
                <Label htmlFor={`file-${serviceImage.id}`}>Upload New Image</Label>
                <div className="flex gap-2">
                  <Input
                    id={`file-${serviceImage.id}`}
                    type="file"
                    accept="image/*"
                    ref={(el) => fileInputRefs.current[serviceImage.id] = el}
                    onChange={(e) => {
                      const file = e.target.files?.[0];
                      if (file) {
                        handleFileUpload(serviceImage.id, file);
                      }
                    }}
                    className="flex-1"
                  />
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => fileInputRefs.current[serviceImage.id]?.click()}
                  >
                    <Upload className="h-4 w-4" />
                  </Button>
                </div>
              </div>

              {/* Position Controls */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor={`pos-x-${serviceImage.id}`}>X Position (%)</Label>
                  <Input
                    id={`pos-x-${serviceImage.id}`}
                    type="range"
                    min="0"
                    max="100"
                    value={serviceImage.position.x}
                    onChange={(e) => updateImagePosition(serviceImage.id, parseInt(e.target.value), serviceImage.position.y)}
                    className="w-full"
                  />
                  <span className="text-sm text-gray-500">{serviceImage.position.x}%</span>
                </div>
                <div>
                  <Label htmlFor={`pos-y-${serviceImage.id}`}>Y Position (%)</Label>
                  <Input
                    id={`pos-y-${serviceImage.id}`}
                    type="range"
                    min="0"
                    max="100"
                    value={serviceImage.position.y}
                    onChange={(e) => updateImagePosition(serviceImage.id, serviceImage.position.x, parseInt(e.target.value))}
                    className="w-full"
                  />
                  <span className="text-sm text-gray-500">{serviceImage.position.y}%</span>
                </div>
              </div>

              {/* Scale Control */}
              <div>
                <Label htmlFor={`scale-${serviceImage.id}`}>Scale</Label>
                <Input
                  id={`scale-${serviceImage.id}`}
                  type="range"
                  min="0.5"
                  max="2"
                  step="0.1"
                  value={serviceImage.scale}
                  onChange={(e) => updateImageScale(serviceImage.id, parseFloat(e.target.value))}
                  className="w-full"
                />
                <span className="text-sm text-gray-500">{serviceImage.scale}x</span>
              </div>

              {/* Reset Button */}
              <Button
                variant="outline"
                size="sm"
                onClick={() => resetImagePosition(serviceImage.id)}
                className="w-full flex items-center gap-2"
              >
                <RotateCcw className="h-4 w-4" />
                Reset Position & Scale
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
};

export default ServiceImagesManager;