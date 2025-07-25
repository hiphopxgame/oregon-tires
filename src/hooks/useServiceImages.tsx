import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';

interface ServiceImage {
  id: string;
  service_key: string;
  title: string;
  image_url: string;
  position_x: number;
  position_y: number;
  scale: number;
  is_current: boolean;
  created_at: string;
  updated_at: string;
}

export const useServiceImages = () => {
  const [currentImages, setCurrentImages] = useState<ServiceImage[]>([]);
  const [imageHistory, setImageHistory] = useState<{ [key: string]: ServiceImage[] }>({});
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  const fetchCurrentImages = async () => {
    try {
      const { data, error } = await supabase
        .from('oretir_service_images')
        .select('*')
        .eq('is_current', true)
        .order('service_key');

      if (error) throw error;
      setCurrentImages(data || []);
    } catch (error) {
      console.error('Error fetching current images:', error);
      toast({
        title: "Error loading images",
        description: "Failed to load current service images",
        variant: "destructive",
      });
    }
  };

  const fetchImageHistory = async (serviceKey: string) => {
    try {
      const { data, error } = await supabase
        .from('oretir_service_images')
        .select('*')
        .eq('service_key', serviceKey)
        .order('created_at', { ascending: false });

      if (error) throw error;
      
      setImageHistory(prev => ({
        ...prev,
        [serviceKey]: data || []
      }));
    } catch (error) {
      console.error('Error fetching image history:', error);
      toast({
        title: "Error loading history",
        description: "Failed to load image history",
        variant: "destructive",
      });
    }
  };

  const uploadServiceImage = async (serviceKey: string, file: File) => {
    try {
      setLoading(true);

      // Upload to Supabase Storage
      const fileExt = file.name.split('.').pop();
      const fileName = `${serviceKey}-${Date.now()}.${fileExt}`;
      const filePath = `service-images/${fileName}`;

      const { error: uploadError } = await supabase.storage
        .from('gallery-images')
        .upload(filePath, file);

      if (uploadError) throw uploadError;

      // Get public URL
      const { data: { publicUrl } } = supabase.storage
        .from('gallery-images')
        .getPublicUrl(filePath);

      // Find current image to copy its settings
      const currentImage = currentImages.find(img => img.service_key === serviceKey);

      // Insert new image record
      const { data, error } = await supabase
        .from('oretir_service_images')
        .insert({
          service_key: serviceKey,
          title: currentImage?.title || serviceKey,
          image_url: publicUrl,
          position_x: currentImage?.position_x || 50,
          position_y: currentImage?.position_y || 50,
          scale: currentImage?.scale || 1.0,
          is_current: false // Initially not current
        })
        .select()
        .single();

      if (error) throw error;

      // Refresh history for this service
      await fetchImageHistory(serviceKey);

      toast({
        title: "Image uploaded successfully",
        description: "The image has been uploaded. Use 'Set as Current' to make it active.",
      });

      return data;
    } catch (error) {
      console.error('Error uploading image:', error);
      toast({
        title: "Upload failed",
        description: "Failed to upload the image. Please try again.",
        variant: "destructive",
      });
      throw error;
    } finally {
      setLoading(false);
    }
  };

  const setCurrentImage = async (imageId: string, serviceKey: string) => {
    try {
      setLoading(true);

      // Start a transaction to update current status
      // First, set all images for this service to not current
      const { error: updateError } = await supabase
        .from('oretir_service_images')
        .update({ is_current: false })
        .eq('service_key', serviceKey);

      if (updateError) throw updateError;

      // Then set the selected image as current
      const { error: setCurrentError } = await supabase
        .from('oretir_service_images')
        .update({ is_current: true })
        .eq('id', imageId);

      if (setCurrentError) throw setCurrentError;

      // Refresh current images
      await fetchCurrentImages();
      await fetchImageHistory(serviceKey);

      toast({
        title: "Image updated",
        description: "The image has been set as current and is now live on the website.",
      });
    } catch (error) {
      console.error('Error setting current image:', error);
      toast({
        title: "Update failed",
        description: "Failed to update the current image.",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const updateImageSettings = async (imageId: string, position_x: number, position_y: number, scale: number) => {
    try {
      const { error } = await supabase
        .from('oretir_service_images')
        .update({ position_x, position_y, scale })
        .eq('id', imageId);

      if (error) throw error;

      // Refresh current images
      await fetchCurrentImages();

      return true;
    } catch (error) {
      console.error('Error updating image settings:', error);
      toast({
        title: "Update failed",
        description: "Failed to update image settings.",
        variant: "destructive",
      });
      return false;
    }
  };

  useEffect(() => {
    const loadData = async () => {
      setLoading(true);
      await fetchCurrentImages();
      setLoading(false);
    };
    loadData();
  }, []);

  return {
    currentImages,
    imageHistory,
    loading,
    fetchImageHistory,
    uploadServiceImage,
    setCurrentImage,
    updateImageSettings,
    refetch: fetchCurrentImages
  };
};