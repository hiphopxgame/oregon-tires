import React, { useState } from 'react';
import { useGalleryImages } from '@/hooks/useGalleryImages';
import { Dialog, DialogContent, DialogClose } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';

interface OregonTiresGalleryProps {
  language: string;
  translations: any;
  primaryColor: string;
}

export const OregonTiresGallery = ({ language, translations, primaryColor }: OregonTiresGalleryProps) => {
  const { images, loading } = useGalleryImages(language);
  const [selectedImageIndex, setSelectedImageIndex] = useState<number | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const openImageModal = (index: number) => {
    setSelectedImageIndex(index);
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setSelectedImageIndex(null);
  };

  const goToPrevious = () => {
    if (selectedImageIndex !== null && selectedImageIndex > 0) {
      setSelectedImageIndex(selectedImageIndex - 1);
    }
  };

  const goToNext = () => {
    if (selectedImageIndex !== null && selectedImageIndex < images.length - 1) {
      setSelectedImageIndex(selectedImageIndex + 1);
    }
  };

  const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'ArrowLeft') goToPrevious();
    if (e.key === 'ArrowRight') goToNext();
    if (e.key === 'Escape') closeModal();
  };

  React.useEffect(() => {
    if (isModalOpen) {
      document.addEventListener('keydown', handleKeyDown);
      return () => document.removeEventListener('keydown', handleKeyDown);
    }
  }, [isModalOpen, selectedImageIndex]);

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
          {images.map((image, index) => (
            <div 
              key={image.id} 
              className="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 cursor-pointer"
              onClick={() => openImageModal(index)}
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

        {/* Image Modal */}
        <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
          <DialogContent className="max-w-4xl max-h-[90vh] p-0 bg-black/90">
            <DialogClose asChild>
              <Button
                variant="ghost"
                size="icon"
                className="absolute top-4 right-4 z-50 text-white hover:bg-white/20"
                onClick={closeModal}
              >
                <X className="h-6 w-6" />
              </Button>
            </DialogClose>
            
            {selectedImageIndex !== null && images[selectedImageIndex] && (
              <div className="relative w-full h-full flex items-center justify-center">
                {/* Navigation buttons */}
                {selectedImageIndex > 0 && (
                  <Button
                    variant="ghost"
                    size="icon"
                    className="absolute left-4 z-50 text-white hover:bg-white/20"
                    onClick={goToPrevious}
                  >
                    <ChevronLeft className="h-8 w-8" />
                  </Button>
                )}
                
                {selectedImageIndex < images.length - 1 && (
                  <Button
                    variant="ghost"
                    size="icon"
                    className="absolute right-4 z-50 text-white hover:bg-white/20"
                    onClick={goToNext}
                  >
                    <ChevronRight className="h-8 w-8" />
                  </Button>
                )}

                {/* Image */}
                <div className="w-full h-full flex flex-col items-center justify-center p-8">
                  <img
                    src={images[selectedImageIndex].image_url}
                    alt={images[selectedImageIndex].title}
                    className="max-w-full max-h-[70vh] object-contain"
                  />
                  <div className="text-center mt-4 text-white">
                    <h3 className="text-xl font-semibold mb-2">
                      {images[selectedImageIndex].title}
                    </h3>
                    {images[selectedImageIndex].description && (
                      <p className="text-gray-300">
                        {images[selectedImageIndex].description}
                      </p>
                    )}
                    <p className="text-sm text-gray-400 mt-2">
                      {selectedImageIndex + 1} of {images.length}
                    </p>
                  </div>
                </div>
              </div>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </section>
  );
};