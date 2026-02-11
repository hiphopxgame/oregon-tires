import React, { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

const SimpleSite: React.FC = () => {
  const location = useLocation();

  useEffect(() => {
    // Check if accessing admin path
    if (location.pathname === '/simple/admin') {
      window.location.href = '/simple/admin.html';
    } else {
      window.location.href = '/simple/index.html';
    }
  }, [location.pathname]);

  return (
    <div className="min-h-screen flex items-center justify-center">
      <p>Redirecting...</p>
    </div>
  );
};

export default SimpleSite;
