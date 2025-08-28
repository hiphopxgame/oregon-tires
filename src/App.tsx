
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import OregonTires from "./pages/OregonTires";
import OregonTiresAdmin from "./pages/OregonTiresAdmin";
import AdminLogin from "./pages/AdminLogin";
import AppointmentBooking from "./pages/AppointmentBooking";
import ProgramarServicio from "./pages/ProgramarServicio";
import Translate from "./pages/Translate";
import NotFound from "./pages/NotFound";
import { EmployeeProfile } from "./pages/EmployeeProfile";
import ProtectedAdminRoute from "./components/ProtectedAdminRoute";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner />
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<OregonTires />} />
          <Route path="/oregon-tires" element={<OregonTires />} />
          <Route path="/admin/login" element={<AdminLogin />} />
          <Route path="/admin" element={
            <ProtectedAdminRoute>
              <OregonTiresAdmin />
            </ProtectedAdminRoute>
          } />
          <Route path="/employee/profile" element={
            <ProtectedAdminRoute>
              <EmployeeProfile />
            </ProtectedAdminRoute>
          } />
          <Route path="/book-appointment" element={<AppointmentBooking />} />
          <Route path="/programar-servicio" element={<ProgramarServicio />} />
          <Route path="/translate" element={<Translate />} />
          {/* ADD ALL CUSTOM ROUTES ABOVE THE CATCH-ALL "*" ROUTE */}
          <Route path="*" element={<NotFound />} />
        </Routes>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
