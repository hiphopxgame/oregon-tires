
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';

interface AdminHeaderProps {
  language: string;
  toggleLanguage: () => void;
  currentView: string;
  setCurrentView: (view: string) => void;
}

export const AdminHeader = ({ 
  language, 
  toggleLanguage, 
  currentView, 
  setCurrentView 
}: AdminHeaderProps) => {
  const navItems = [
    { id: 'calendar', label: 'Calendar' },
    { id: 'appointments', label: 'Appointments' },
    { id: 'messages', label: 'Messages' },
    { id: 'employees', label: 'Employees' },
    { id: 'gallery', label: 'Gallery' },
    { id: 'analytics', label: 'Analytics' }
  ];

  return (
    <header style={{ backgroundColor: '#007030' }} className="text-white shadow-lg">
      <div className="container mx-auto px-4 py-6">
        <div className="flex justify-between items-center mb-4">
          <div>
            <Link to="/" className="hover:opacity-80">
              <h1 className="text-3xl font-bold">Oregon Tires Admin</h1>
              <p className="text-white/80">Management Dashboard</p>
            </Link>
          </div>
          <button 
            onClick={toggleLanguage} 
            className="text-white hover:text-yellow-200"
          >
            English | Español
          </button>
        </div>
        
        <nav className="flex flex-wrap gap-2">
          {navItems.map((item) => (
            <Button
              key={item.id}
              variant={currentView === item.id ? "secondary" : "ghost"}
              onClick={() => setCurrentView(item.id)}
              className={`${
                currentView === item.id 
                  ? "bg-white text-green-700 hover:bg-gray-100" 
                  : "text-white hover:bg-green-600"
              }`}
            >
              {item.label}
            </Button>
          ))}
        </nav>
      </div>
    </header>
  );
};
