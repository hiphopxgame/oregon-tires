import React, { useEffect } from 'react';

const SimpleSite: React.FC = () => {
  useEffect(() => {
    // Redirect to the static HTML file
    window.location.href = '/simple/index.html';
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center">
      <p>Redirecting to simplified site...</p>
    </div>
  );
};

export default SimpleSite;
