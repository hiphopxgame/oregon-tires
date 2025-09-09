import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';

interface ServiceImage {
  service_key: string;
  image_url: string;
  position_x: number;
  position_y: number;
  scale: number;
}

// Fallback images served from public folder to avoid build-time imports
const fallbackImages: Record<string, string> = {
  'hero-background': '/lovable-uploads/afc0de17-b407-4b29-b6a2-6f44d5dcad0d.png',
  'expert-technicians': '/images/expert-technicians.jpg',
  'fast-cars': '/images/fast-cars.jpg',
  'quality-car-parts': '/images/quality-parts.jpg',
  'bilingual-support': '/images/bilingual-service.jpg',
  'tire-shop': '/images/tire-services.jpg',
  'auto-repair': '/images/auto-maintenance.jpg',
  'specialized-tools': '/images/specialized-services.jpg',
};

export const useServiceImagesForFrontend = () => {
  const [serviceImages, setServiceImages] = useState<{ [key: string]: ServiceImage }>({});
  const [loading, setLoading] = useState(true);

  const fetchServiceImages = async () => {
    try {
      setLoading(true);
      
      const { data, error } = await supabase
        .from('oretir_service_images')
        .select('service_key, image_url, position_x, position_y, scale')
        .eq('is_current', true);

      if (error) throw error;

      // Convert array to object for easy lookup
      const imageMap: { [key: string]: ServiceImage } = {};
      data?.forEach(img => {
        imageMap[img.service_key] = img;
      });

      setServiceImages(imageMap);
    } catch (error) {
      console.error('Error fetching service images:', error);
      // On error, we'll use fallback images
      setServiceImages({});
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchServiceImages();

    // Subscribe to real-time updates with unique channel name
    const channelId = Math.random().toString(36).substr(2, 9);
    const channel = supabase
      .channel(`service-images-changes-${channelId}`)
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'oretir_service_images',
        },
        () => {
          fetchServiceImages();
        }
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, []);

  const getImageUrl = (serviceKey: string): string => {
    const dbImage = serviceImages[serviceKey];
    return dbImage?.image_url || fallbackImages[serviceKey as keyof typeof fallbackImages] || '';
  };

  const getImageStyle = (serviceKey: string) => {
    const dbImage = serviceImages[serviceKey];
    if (!dbImage) {
      return {
        backgroundPosition: 'center',
        transform: 'scale(1)',
      };
    }

    return {
      backgroundPosition: `${dbImage.position_x}% ${dbImage.position_y}%`,
      transform: `scale(${dbImage.scale})`,
    };
  };

  return {
    getImageUrl,
    getImageStyle,
    loading,
    refetch: fetchServiceImages
  };
};