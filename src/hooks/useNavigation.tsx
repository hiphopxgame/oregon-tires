
import { useState } from 'react';

export const useNavigation = () => {
  const scrollToSection = (id: string) => {
    const element = document.getElementById(id);
    if (element) {
      const headerHeight = 120;
      const elementPosition = element.offsetTop - headerHeight;
      window.scrollTo({ 
        top: elementPosition, 
        behavior: 'smooth' 
      });
    }
  };

  const openContactForm = () => {
    scrollToSection('contact');
  };

  const openScheduleForm = () => {
    // Redirect to the dedicated booking page
    window.location.href = '/book-appointment';
  };

  return {
    scrollToSection,
    openContactForm,
    openScheduleForm
  };
};
