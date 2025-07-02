
import { useState } from 'react';
import translations from "@/utils/translations";

export const useLanguage = () => {
  const [language, setLanguage] = useState('english');
  const [t, setT] = useState(translations['english']);

  const toggleLanguage = () => {
    const newLanguage = language === 'english' ? 'spanish' : 'english';
    setLanguage(newLanguage);
    setT(translations[newLanguage]);
  };

  return {
    language,
    t,
    toggleLanguage
  };
};
