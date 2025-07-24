import { useLocation } from "react-router-dom";
import { useEffect } from "react";

const NotFound = () => {
  const location = useLocation();

  useEffect(() => {
    console.error(
      "404 Error: User attempted to access non-existent route:",
      location.pathname
    );
  }, [location.pathname]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="text-center max-w-md mx-auto px-4">
        {/* Logo */}
        <div className="mb-8">
          <img 
            src="/lovable-uploads/1290fb5e-e45c-4fc3-b523-e71d756ec1ef.png" 
            alt="Oregon Tires Auto Care" 
            className="h-20 w-auto mx-auto"
          />
        </div>
        
        <h1 className="text-6xl font-bold text-[#007030] mb-4">404</h1>
        <h2 className="text-2xl font-semibold text-gray-800 mb-4">Page Not Found</h2>
        <p className="text-gray-600 mb-8">
          Sorry, the page you're looking for doesn't exist or has been moved.
        </p>
        
        <a 
          href="/" 
          className="inline-block bg-[#007030] text-white px-6 py-3 rounded-lg font-medium hover:bg-[#005a24] transition-colors"
        >
          Return to Home
        </a>
      </div>
    </div>
  );
};

export default NotFound;
