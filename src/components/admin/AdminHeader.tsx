
import { Button } from '@/components/ui/button';
import { Link } from 'react-router-dom';

interface AdminHeaderProps {
  language: string;
  toggleLanguage: () => void;
  currentView: string;
  setCurrentView: (view: string) => void;
  t: any;
}

export const AdminHeader = ({ 
  language, 
  toggleLanguage, 
  currentView, 
  setCurrentView,
  t 
}: AdminHeaderProps) => {
  const navItems = [
    { id: 'overview', label: t.admin.overview },
    { id: 'calendar', label: t.admin.calendar },
    { id: 'appointments', label: t.admin.appointments },
    { id: 'messages', label: t.admin.messages },
    { id: 'emails', label: t.admin.emailLogs },
    { id: 'employees', label: t.admin.employees },
    { id: 'gallery', label: t.admin.gallery },
    { id: 'analytics', label: t.admin.analytics }
  ];

  return (
    <header style={{ backgroundColor: '#007030' }} className="text-white shadow-lg">
      <div className="container mx-auto px-4 py-6">
        <div className="flex justify-between items-center mb-4">
          <Link to="/" className="hover:opacity-80 transition-opacity flex items-center gap-3">
            <img 
              src="/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png" 
              alt="Oregon Tires Auto Care" 
              className="h-12 w-auto"
            />
            <div>
              <h1 className="text-2xl font-bold">{t.admin.dashboard}</h1>
              <p className="text-white/80 text-sm">{t.admin.managementDashboard}</p>
            </div>
          </Link>
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
