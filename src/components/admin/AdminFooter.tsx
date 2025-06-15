
import { ExternalLink } from 'lucide-react';

interface AdminFooterProps {
  language: string;
  toggleLanguage: () => void;
}

export const AdminFooter = ({ language, toggleLanguage }: AdminFooterProps) => {
  return (
    <footer style={{ backgroundColor: '#007030' }} className="text-white py-4 mt-12">
      <div className="container mx-auto px-4 flex justify-between items-center">
        <div className="flex items-center gap-6">
          <p>Oregon Tires Management System - &copy; 2025 All rights reserved</p>
          <a 
            href="/oregon-tires-dashboard.html" 
            target="_blank"
            rel="noopener noreferrer"
            className="text-white hover:text-yellow-200 flex items-center gap-2 text-sm"
          >
            <ExternalLink className="h-4 w-4" />
            Static Dashboard
          </a>
        </div>
        <button 
          onClick={toggleLanguage} 
          className="text-white hover:text-yellow-200"
        >
          🇺🇸 English | 🇲🇽 Español
        </button>
      </div>
    </footer>
  );
};
