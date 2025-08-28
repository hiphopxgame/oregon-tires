import { Navigate } from 'react-router-dom';

const Index = () => {
  // Redirect to Oregon Tires main page
  return <Navigate to="/oregon-tires" replace />;
};

export default Index;
