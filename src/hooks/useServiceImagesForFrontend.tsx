import { useState, useEffect } from 'react';
import { supabase } from '@/integrations/supabase/client';

interface ServiceImage {
  service_key: string;
  image_url: string;
  position_x: number;
  position_y: number;
  scale: number;
}

// Import fallback images
import expertTechniciansImg from '@/assets/expert-technicians.jpg';
import fastCarsImg from '@/assets/fast-cars.jpg';
import qualityCarPartsImg from '@/assets/quality-car-parts.jpg';
import bilingualSupportImg from '@/assets/bilingual-support.jpg';
import tireShopImg from '@/assets/tire-shop.jpg';
import autoRepairImg from '@/assets/auto-repair.jpg';
import specializedToolsImg from '@/assets/specialized-tools.jpg';

const fallbackImages = {
  'expert-technicians': expertTechniciansImg,
  'fast-cars': fastCarsImg,
  'quality-car-parts': qualityCarPartsImg,
  'bilingual-support': bilingualSupportImg,
  'tire-shop': tireShopImg,
  'auto-repair': autoRepairImg,
  'specialized-tools': specializedToolsImg,
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

    // Subscribe to real-time updates
    const channel = supabase
      .channel('service-images-changes')
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