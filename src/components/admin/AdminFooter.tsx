
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
