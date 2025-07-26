import React, { useState, useRef } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Upload, Save, RotateCcw, History, Check, Clock, Image as ImageIcon, Trash2, Eye } from 'lucide-react';
import { useToast } from '@/hooks/use-toast';
import { useServiceImages } from '@/hooks/useServiceImages';
import { supabase } from '@/integrations/supabase/client';
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

// Define the service keys that match the home page services
const homePageServices = [
  { key: 'expert-technicians', title: 'Expert Technicians', assetName: 'expert-technicians' },
  { key: 'fast-cars', title: 'Quick Service', assetName: 'fast-cars' },
  { key: 'quality-car-parts', title: 'Quality Parts', assetName: 'quality-car-parts' },
  { key: 'bilingual-support', title: 'Bilingual Support', assetName: 'bilingual-support' },
  { key: 'tire-shop', title: 'Tire Services', assetName: 'tire-shop' },
  { key: 'auto-repair', title: 'Auto Maintenance', assetName: 'auto-repair' },
  { key: 'specialized-tools', title: 'Specialized Services', assetName: 'specialized-tools' }
];

const ServiceImagesManager = () => {
  const {
    currentImages,
    imageHistory,
    loading,
    fetchImageHistory,
    uploadServiceImage,
    setCurrentImage,
    updateImageSettings,
    refetch: fetchCurrentImages
  } = useServiceImages();

  const fileInputRefs = useRef<{ [key: string]: HTMLInputElement | null }>({});
  const [selectedFiles, setSelectedFiles] = useState<{ [key: string]: File }>({});
  const [uploading, setUploading] = useState<{ [key: string]: boolean }>({});
  const { toast } = useToast();

  // Filter to only show services that are on the home page
  const homePageImages = currentImages.filter(img => 
    homePageServices.some(service => service.key === img.service_key)
  );

  const handleFileSelect = (serviceKey: string, file: File | null) => {
    if (file) {
      setSelectedFiles(prev => ({ ...prev, [serviceKey]: file }));
    } else {
      setSelectedFiles(prev => {
        const newFiles = { ...prev };
        delete newFiles[serviceKey];
        return newFiles;
      });
    }
  };

  const handleUploadImage = async (serviceKey: string) => {
    const file = selectedFiles[serviceKey];
    if (!file) return;

    try {
      setUploading(prev => ({ ...prev, [serviceKey]: true }));
      await uploadServiceImage(serviceKey, file);
      
      // Clear the selected file after successful upload
      setSelectedFiles(prev => {
        const newFiles = { ...prev };
        delete newFiles[serviceKey];
        return newFiles;
      });
      
      // Reset the file input
      if (fileInputRefs.current[serviceKey]) {
        fileInputRefs.current[serviceKey]!.value = '';
      }
      
      toast({
        title: "Upload successful!",
        description: "Your image has been uploaded. Use 'Set as Current' to make it live.",
      });
    } catch (error) {
      // Error is handled in the hook
    } finally {
      setUploading(prev => ({ ...prev, [serviceKey]: false }));
    }
  };

  const handleOpenHistory = async (serviceKey: string) => {
    await fetchImageHistory(serviceKey);
  };

  const handleRestoreImage = async (imageId: string, serviceKey: string) => {
    try {
      await setCurrentImage(imageId, serviceKey);
      toast({
        title: "Image updated",
        description: "The image is now live on the website!",
      });
    } catch (error) {
      // Error is handled in the hook
    }
  };

  const deleteImage = async (imageId: string, imageUrl: string) => {
    try {
      // Delete from storage
      const pathMatch = imageUrl.match(/service-images\/(.+)$/);
      if (pathMatch) {
        const { error: storageError } = await supabase.storage
          .from('gallery-images')
          .remove([`service-images/${pathMatch[1]}`]);
        
        if (storageError) throw storageError;
      }

      // Delete from database
      const { error } = await supabase
        .from('oretir_service_images')
        .delete()
        .eq('id', imageId);

      if (error) throw error;

      // Refresh data
      await fetchCurrentImages();
      
      toast({
        title: "Image deleted",
        description: "The image has been permanently deleted.",
      });
    } catch (error) {
      console.error('Error deleting image:', error);
      toast({
        title: "Delete failed",
        description: "Failed to delete the image.",
        variant: "destructive",
      });
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
          <p className="text-gray-600 mt-1">Manage the 7 live service images displayed on the home page</p>
        </div>
      </div>

      {homePageImages.length === 0 && !loading && (
        <div className="col-span-full bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
          <ImageIcon className="h-12 w-12 text-blue-400 mx-auto mb-4" />
          <h3 className="text-lg font-semibold text-blue-800 mb-2">No Service Images Found</h3>
          <p className="text-blue-600 mb-4">
            The service images from the home page are not yet in the database. 
            You need to upload images for each service to start managing them.
          </p>
          <div className="text-left max-w-md mx-auto">
            <p className="text-sm text-blue-700 font-medium mb-2">Expected services:</p>
            <ul className="text-sm text-blue-600 space-y-1">
              {homePageServices.map(service => (
                <li key={service.key}>• {service.title}</li>
              ))}
            </ul>
          </div>
        </div>
      )}

      <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        {homePageImages.map((serviceImage) => (
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
                              <div className="flex gap-1">
                                {!historyImage.is_current && (
                                  <Button
                                    size="sm"
                                    variant="outline"
                                    className="flex-1"
                                    onClick={() => handleRestoreImage(historyImage.id, serviceImage.service_key)}
                                  >
                                    <Eye className="h-3 w-3 mr-1" />
                                    Set Live
                                  </Button>
                                )}
                                <Button
                                  size="sm"
                                  variant="outline"
                                  className={historyImage.is_current ? "w-full" : "flex-1"}
                                  onClick={() => deleteImage(historyImage.id, historyImage.image_url)}
                                  disabled={historyImage.is_current}
                                >
                                  <Trash2 className="h-3 w-3 mr-1" />
                                  Delete
                                </Button>
                              </div>
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
                <div className="absolute top-2 right-2 flex gap-2">
                  <Badge className="bg-green-500">
                    <Clock className="h-3 w-3 mr-1" />
                    Live
                  </Badge>
                </div>
              </div>

              {/* Upload New Image */}
              <div className="space-y-3">
                <Label htmlFor={`file-${serviceImage.service_key}`}>Upload New Image</Label>
                <div className="space-y-3">
                  <div className="flex gap-2">
                    <Input
                      id={`file-${serviceImage.service_key}`}
                      type="file"
                      accept="image/*"
                      ref={(el) => fileInputRefs.current[serviceImage.service_key] = el}
                      onChange={(e) => {
                        const file = e.target.files?.[0] || null;
                        handleFileSelect(serviceImage.service_key, file);
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
                  
                  {selectedFiles[serviceImage.service_key] && (
                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <ImageIcon className="h-4 w-4 text-blue-600" />
                          <span className="text-sm font-medium text-blue-800">
                            {selectedFiles[serviceImage.service_key].name}
                          </span>
                        </div>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => handleFileSelect(serviceImage.service_key, null)}
                          >
                            Cancel
                          </Button>
                          <Button
                            size="sm"
                            onClick={() => handleUploadImage(serviceImage.service_key)}
                            disabled={uploading[serviceImage.service_key]}
                          >
                            {uploading[serviceImage.service_key] ? (
                              <>
                                <div className="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-1" />
                                Uploading...
                              </>
                            ) : (
                              <>
                                <Upload className="h-3 w-3 mr-1" />
                                Upload Image
                              </>
                            )}
                          </Button>
                        </div>
                      </div>
                    </div>
                  )}
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

              {/* Action Buttons */}
              <div className="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => resetSettings(serviceImage.service_key)}
                  className="flex-1 flex items-center gap-2"
                >
                  <RotateCcw className="h-4 w-4" />
                  Reset
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handleOpenHistory(serviceImage.service_key)}
                  className="flex-1 flex items-center gap-2"
                >
                  <History className="h-4 w-4" />
                  Manage
                </Button>
              </div>

              {/* Image Info */}
              <div className="text-xs text-gray-500 pt-2 border-t">
                <p>Last updated: {new Date(serviceImage.updated_at).toLocaleDateString()}</p>
              </div>
            </CardContent>
          </Card>
        ))}
        
        {/* Upload sections for missing services */}
        {homePageServices
          .filter(service => !currentImages.some(img => img.service_key === service.key))
          .map(service => (
            <Card key={service.key} className="overflow-hidden border-dashed border-2 border-gray-300">
              <CardHeader className="pb-4">
                <CardTitle className="text-lg text-gray-600">{service.title}</CardTitle>
                <p className="text-sm text-gray-500">No image uploaded yet</p>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="relative h-48 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                  <div className="text-center">
                    <ImageIcon className="h-12 w-12 text-gray-400 mx-auto mb-2" />
                    <p className="text-gray-500">Upload an image to get started</p>
                  </div>
                </div>

                {/* Upload New Image */}
                <div className="space-y-3">
                  <Label htmlFor={`file-${service.key}`}>Upload Image</Label>
                  <div className="space-y-3">
                    <div className="flex gap-2">
                      <Input
                        id={`file-${service.key}`}
                        type="file"
                        accept="image/*"
                        ref={(el) => fileInputRefs.current[service.key] = el}
                        onChange={(e) => {
                          const file = e.target.files?.[0] || null;
                          handleFileSelect(service.key, file);
                        }}
                        className="flex-1"
                      />
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => fileInputRefs.current[service.key]?.click()}
                      >
                        <Upload className="h-4 w-4" />
                      </Button>
                    </div>
                    
                    {selectedFiles[service.key] && (
                      <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-2">
                            <ImageIcon className="h-4 w-4 text-blue-600" />
                            <span className="text-sm font-medium text-blue-800">
                              {selectedFiles[service.key].name}
                            </span>
                          </div>
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => handleFileSelect(service.key, null)}
                            >
                              Cancel
                            </Button>
                            <Button
                              size="sm"
                              onClick={() => handleUploadImage(service.key)}
                              disabled={uploading[service.key]}
                            >
                              {uploading[service.key] ? (
                                <>
                                  <div className="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-1" />
                                  Uploading...
                                </>
                              ) : (
                                <>
                                  <Upload className="h-3 w-3 mr-1" />
                                  Upload Image
                                </>
                              )}
                            </Button>
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          ))
        }
      </div>
    </div>
  );
};

export default ServiceImagesManager;