
import { useState, useEffect } from 'react';

interface DesignTheme {
  primaryColor: string;
  secondaryColor: string;
  backgroundColor: string;
  textColor: string;
}

export const useDesignTheme = () => {
  const [currentDesign, setCurrentDesign] = useState<DesignTheme>({
    primaryColor: '#007030',
    secondaryColor: '#FEE11A',
    backgroundColor: '#f0fdf4',
    textColor: '#000000',
  });

  useEffect(() => {
    const storedDesign = localStorage.getItem('currentDesign');
    if (storedDesign) {
      setCurrentDesign(JSON.parse(storedDesign));
    }
  }, []);

  return { currentDesign, setCurrentDesign };
};
