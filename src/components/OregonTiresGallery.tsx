import React from 'react';
import { useGalleryImages } from '@/hooks/useGalleryImages';

interface OregonTiresGalleryProps {
  language: string;
  translations: any;
  primaryColor: string;
}

export const OregonTiresGallery = ({ language, translations, primaryColor }: OregonTiresGalleryProps) => {
  const { images, loading } = useGalleryImages(language);

  if (loading) {
    return (
      <section className="py-16" style={{ backgroundColor: '#f8f9fa' }}>
        <div className="container mx-auto px-4">
          <div className="text-center">
            <div className="text-gray-600">Loading gallery...</div>
          </div>
        </div>
      </section>
    );
  }

  if (images.length === 0) {
    return null; // Don't show the section if no images
  }

  return (
    <section className="py-16" style={{ backgroundColor: '#f8f9fa' }}>
      <div className="container mx-auto px-4">
        <div className="text-center mb-12">
          <h2 className="text-4xl font-bold mb-4" style={{ color: primaryColor }}>
            {translations.gallery?.title || 'Our Work'}
          </h2>
          <p className="text-gray-600 max-w-2xl mx-auto">
            {translations.gallery?.subtitle || 'Take a look at some of our recent tire and automotive service work.'}
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {images.map((image) => (
            <div 
              key={image.id} 
              className="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
            >
              <div className="aspect-w-16 aspect-h-12 relative overflow-hidden">
                <img 
                  src={image.image_url} 
                  alt={image.title}
                  className="w-full h-64 object-cover hover:scale-105 transition-transform duration-300"
                />
              </div>
              <div className="p-4">
                <h3 className="font-semibold text-lg mb-2" style={{ color: primaryColor }}>
                  {image.title}
                </h3>
                {image.description && (
                  <p className="text-gray-600 text-sm">
                    {image.description}
                  </p>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};