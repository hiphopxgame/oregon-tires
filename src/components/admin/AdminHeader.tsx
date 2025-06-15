
import { Link } from 'react-router-dom';

interface AdminHeaderProps {
  language: string;
  toggleLanguage: () => void;
  currentView: string;
  setCurrentView: (view: string) => void;
}

export const AdminHeader = ({ language, toggleLanguage, currentView, setCurrentView }: AdminHeaderProps) => {
  const navItems = [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'appointments', label: 'Appointments' },
    { id: 'messages', label: 'Messages' },
    { id: 'analytics', label: 'Analytics' },
  ];

  return (
    <header style={{ backgroundColor: '#007030' }} className="text-white py-6 shadow-lg">
      <div className="container mx-auto px-4">
        <div className="flex justify-between items-center mb-4">
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
        
        {/* Internal Navigation */}
        <nav className="flex space-x-6">
          {navItems.map((item) => (
            <button
              key={item.id}
              onClick={() => setCurrentView(item.id)}
              className={`px-4 py-2 rounded-md transition-colors ${
                currentView === item.id
                  ? 'bg-white text-green-700 font-semibold'
                  : 'text-white hover:bg-green-600'
              }`}
            >
              {item.label}
            </button>
          ))}
        </nav>
      </div>
    </header>
  );
};
