import React, { useState, useRef } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Upload, Save, RotateCcw, History, Check, Clock, Image as ImageIcon } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { useServiceImages } from '@/hooks/useServiceImages';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Badge } from "@/components/ui/badge";

const ServiceImagesManager = () => {
  const {
    currentImages,
    imageHistory,
    loading,
    fetchImageHistory,
    uploadServiceImage,
    setCurrentImage,
    updateImageSettings
  } = useServiceImages();

  const fileInputRefs = useRef<{ [key: string]: HTMLInputElement | null }>({});
  const { toast } = useToast();

  const handleFileUpload = async (serviceKey: string, file: File) => {
    try {
      await uploadServiceImage(serviceKey, file);
    } catch (error) {
      // Error is handled in the hook
    }
  };

  const handleOpenHistory = async (serviceKey: string) => {
    await fetchImageHistory(serviceKey);
  };

  const handleRestoreImage = async (imageId: string, serviceKey: string) => {
    try {
      await setCurrentImage(imageId, serviceKey);
      toast({
        title: "Image restored",
        description: "The selected image has been restored as the current image.",
      });
    } catch (error) {
      // Error is handled in the hook
    }
  };

  const updatePosition = (serviceKey: string, x: number, y: number) => {
    const currentImage = currentImages.find(img => img.service_key === serviceKey);
    if (currentImage) {
      updateImageSettings(currentImage.id, x, y, currentImage.scale);
    }
  };

  const updateScale = (serviceKey: string, scale: number) => {
    const currentImage = currentImages.find(img => img.service_key === serviceKey);
    if (currentImage) {
      updateImageSettings(currentImage.id, currentImage.position_x, currentImage.position_y, scale);
    }
  };

  const resetSettings = (serviceKey: string) => {
    const currentImage = currentImages.find(img => img.service_key === serviceKey);
    if (currentImage) {
      updateImageSettings(currentImage.id, 50, 50, 1.0);
    }
  };

  if (loading && currentImages.length === 0) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-gray-500">Loading service images...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-2xl font-bold">Service Images Manager</h2>
          <p className="text-gray-600 mt-1">Manage images for the services section with history tracking</p>
        </div>
      </div>

      <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        {currentImages.map((serviceImage) => (
          <Card key={serviceImage.service_key} className="overflow-hidden">
            <CardHeader className="pb-4">
              <div className="flex justify-between items-start">
                <CardTitle className="text-lg">{serviceImage.title}</CardTitle>
                <Dialog>
                  <DialogTrigger asChild>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleOpenHistory(serviceImage.service_key)}
                      className="flex items-center gap-2"
                    >
                      <History className="h-4 w-4" />
                      History
                    </Button>
                  </DialogTrigger>
                  <DialogContent className="max-w-4xl max-h-[80vh]">
                    <DialogHeader>
                      <DialogTitle>Image History - {serviceImage.title}</DialogTitle>
                      <DialogDescription>
                        View and restore previous versions of this service image
                      </DialogDescription>
                    </DialogHeader>
                    <ScrollArea className="max-h-[60vh]">
                      <div className="grid grid-cols-2 md:grid-cols-3 gap-4 p-4">
                        {imageHistory[serviceImage.service_key]?.map((historyImage) => (
                          <div key={historyImage.id} className="relative group">
                            <div className="aspect-video bg-gray-100 rounded-lg overflow-hidden relative">
                              <img
                                src={historyImage.image_url}
                                alt={`${serviceImage.title} version`}
                                className="w-full h-full object-cover"
                                style={{
                                  objectPosition: `${historyImage.position_x}% ${historyImage.position_y}%`
                                }}
                              />
                              <div className="absolute inset-0 bg-black/20" />
                              {historyImage.is_current && (
                                <Badge className="absolute top-2 left-2 bg-green-500">
                                  <Check className="h-3 w-3 mr-1" />
                                  Current
                                </Badge>
                              )}
                            </div>
                            <div className="mt-2 space-y-2">
                              <p className="text-xs text-gray-500">
                                {new Date(historyImage.created_at).toLocaleDateString()} at{' '}
                                {new Date(historyImage.created_at).toLocaleTimeString()}
                              </p>
                              {!historyImage.is_current && (
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className="w-full"
                                  onClick={() => handleRestoreImage(historyImage.id, serviceImage.service_key)}
                                >
                                  <RotateCcw className="h-3 w-3 mr-1" />
                                  Restore
                                </Button>
                              )}
                            </div>
                          </div>
                        ))}
                      </div>
                    </ScrollArea>
                  </DialogContent>
                </Dialog>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Current Image Preview */}
              <div className="relative h-48 bg-gray-100 rounded-lg overflow-hidden">
                <div 
                  className="absolute inset-0 bg-cover transition-all duration-300"
                  style={{ 
                    backgroundImage: `url(${serviceImage.image_url})`,
                    backgroundPosition: `${serviceImage.position_x}% ${serviceImage.position_y}%`,
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
                <Badge className="absolute top-2 right-2 bg-green-500">
                  <Clock className="h-3 w-3 mr-1" />
                  Live
                </Badge>
              </div>

              {/* Upload New Image */}
              <div className="space-y-2">
                <Label htmlFor={`file-${serviceImage.service_key}`}>Upload New Image</Label>
                <div className="flex gap-2">
                  <Input
                    id={`file-${serviceImage.service_key}`}
                    type="file"
                    accept="image/*"
                    ref={(el) => fileInputRefs.current[serviceImage.service_key] = el}
                    onChange={(e) => {
                      const file = e.target.files?.[0];
                      if (file) {
                        handleFileUpload(serviceImage.service_key, file);
                      }
                    }}
                    className="flex-1"
                  />
                  <Button
                    size="sm"
                    variant="outline"
                    onClick={() => fileInputRefs.current[serviceImage.service_key]?.click()}
                  >
                    <Upload className="h-4 w-4" />
                  </Button>
                </div>
              </div>

              {/* Position Controls */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor={`pos-x-${serviceImage.service_key}`}>X Position (%)</Label>
                  <Input
                    id={`pos-x-${serviceImage.service_key}`}
                    type="range"
                    min="0"
                    max="100"
                    value={serviceImage.position_x}
                    onChange={(e) => updatePosition(serviceImage.service_key, parseInt(e.target.value), serviceImage.position_y)}
                    className="w-full"
                  />
                  <span className="text-sm text-gray-500">{serviceImage.position_x}%</span>
                </div>
                <div>
                  <Label htmlFor={`pos-y-${serviceImage.service_key}`}>Y Position (%)</Label>
                  <Input
                    id={`pos-y-${serviceImage.service_key}`}
                    type="range"
                    min="0"
                    max="100"
                    value={serviceImage.position_y}
                    onChange={(e) => updatePosition(serviceImage.service_key, serviceImage.position_x, parseInt(e.target.value))}
                    className="w-full"
                  />
                  <span className="text-sm text-gray-500">{serviceImage.position_y}%</span>
                </div>
              </div>

              {/* Scale Control */}
              <div>
                <Label htmlFor={`scale-${serviceImage.service_key}`}>Scale</Label>
                <Input
                  id={`scale-${serviceImage.service_key}`}
                  type="range"
                  min="0.5"
                  max="2"
                  step="0.1"
                  value={serviceImage.scale}
                  onChange={(e) => updateScale(serviceImage.service_key, parseFloat(e.target.value))}
                  className="w-full"
                />
                <span className="text-sm text-gray-500">{serviceImage.scale}x</span>
              </div>

              {/* Reset Button */}
              <Button
                variant="outline"
                size="sm"
                onClick={() => resetSettings(serviceImage.service_key)}
                className="w-full flex items-center gap-2"
              >
                <RotateCcw className="h-4 w-4" />
                Reset Position & Scale
              </Button>

              {/* Image Info */}
              <div className="text-xs text-gray-500 pt-2 border-t">
                <p>Last updated: {new Date(serviceImage.updated_at).toLocaleDateString()}</p>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
};

export default ServiceImagesManager;