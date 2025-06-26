
import { useState } from 'react';

export const useNavigation = () => {
  const scrollToSection = (id: string) => {
    const element = document.getElementById(id);
    if (element) {
      // Get the actual header height dynamically
      const header = document.querySelector('header');
      const headerHeight = header ? header.offsetHeight + 20 : 140; // Add extra padding
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
