import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';

export interface GalleryImage {
  id: string;
  title: string;
  description?: string;
  image_url: string;
  language: string;
  display_order: number;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export const useGalleryImages = (language?: string) => {
  const [images, setImages] = useState<GalleryImage[]>([]);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  const fetchImages = async () => {
    try {
      let query = supabase
        .from('oretir_gallery_images')
        .select('*')
        .eq('is_active', true)
        .order('display_order', { ascending: true })
        .order('created_at', { ascending: false });

      if (language) {
        query = query.eq('language', language);
      }

      const { data, error } = await query;

      if (error) throw error;
      setImages(data || []);
    } catch (error) {
      console.error('Error fetching gallery images:', error);
      toast({
        title: "Error",
        description: "Failed to load gallery images",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const uploadImage = async (file: File): Promise<string | null> => {
    try {
      const fileExt = file.name.split('.').pop();
      const fileName = `${Date.now()}.${fileExt}`;
      const filePath = `gallery/${fileName}`;

      const { error: uploadError } = await supabase.storage
        .from('gallery-images')
        .upload(filePath, file);

      if (uploadError) throw uploadError;

      const { data: { publicUrl } } = supabase.storage
        .from('gallery-images')
        .getPublicUrl(filePath);

      return publicUrl;
    } catch (error) {
      console.error('Error uploading image:', error);
      toast({
        title: "Error",
        description: "Failed to upload image",
        variant: "destructive",
      });
      return null;
    }
  };

  const addImage = async (imageData: Omit<GalleryImage, 'id' | 'created_at' | 'updated_at'>) => {
    try {
      const { error } = await supabase
        .from('oretir_gallery_images')
        .insert([imageData]);

      if (error) throw error;

      toast({
        title: "Success",
        description: "Image added to gallery",
      });

      fetchImages();
    } catch (error) {
      console.error('Error adding image:', error);
      toast({
        title: "Error",
        description: "Failed to add image",
        variant: "destructive",
      });
    }
  };

  const updateImage = async (id: string, updates: Partial<GalleryImage>) => {
    try {
      const { error } = await supabase
        .from('oretir_gallery_images')
        .update(updates)
        .eq('id', id);

      if (error) throw error;

      toast({
        title: "Success",
        description: "Image updated successfully",
      });

      fetchImages();
    } catch (error) {
      console.error('Error updating image:', error);
      toast({
        title: "Error",
        description: "Failed to update image",
        variant: "destructive",
      });
    }
  };

  const deleteImage = async (id: string, imageUrl: string) => {
    try {
      // Delete from database
      const { error: dbError } = await supabase
        .from('oretir_gallery_images')
        .delete()
        .eq('id', id);

      if (dbError) throw dbError;

      // Delete from storage
      const filePath = imageUrl.split('/').pop();
      if (filePath) {
        await supabase.storage
          .from('gallery-images')
          .remove([`gallery/${filePath}`]);
      }

      toast({
        title: "Success",
        description: "Image deleted successfully",
      });

      fetchImages();
    } catch (error) {
      console.error('Error deleting image:', error);
      toast({
        title: "Error",
        description: "Failed to delete image",
        variant: "destructive",
      });
    }
  };

  useEffect(() => {
    fetchImages();
  }, [language]);

  return {
    images,
    loading,
    uploadImage,
    addImage,
    updateImage,
    deleteImage,
    refetch: fetchImages
  };
};