
import { Link } from 'react-router-dom';

interface AdminHeaderProps {
  language: string;
  toggleLanguage: () => void;
}

export const AdminHeader = ({ language, toggleLanguage }: AdminHeaderProps) => {
  return (
    <header style={{ backgroundColor: '#007030' }} className="text-white py-6 shadow-lg">
      <div className="container mx-auto px-4 flex justify-between items-center">
        <Link to="/" className="inline-block">
          <h1 className="text-3xl font-bold hover:text-yellow-200">Oregon Tires Management</h1>
        </Link>
        <button 
          onClick={toggleLanguage} 
          className="text-white hover:text-yellow-200"
        >
          🇺🇸 English | 🇲🇽 Español
        </button>
      </div>
    </header>
  );
};
