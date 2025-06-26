
import { useState } from 'react';

export const useNavigation = () => {
  const [isScheduleMode, setIsScheduleMode] = useState(false);

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
    setIsScheduleMode(false);
    scrollToSection('contact');
  };

  const openScheduleForm = () => {
    setIsScheduleMode(true);
    scrollToSection('contact');
  };

  return {
    isScheduleMode,
    setIsScheduleMode,
    scrollToSection,
    openContactForm,
    openScheduleForm
  };
};
