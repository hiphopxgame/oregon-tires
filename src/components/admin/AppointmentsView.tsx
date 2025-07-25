
import { AppointmentsTab } from './AppointmentsTab';
import { Appointment } from '@/types/admin';
import { useLanguage } from '@/hooks/useLanguage';

interface AppointmentsViewProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
  updateAppointmentAssignment: (id: string, employeeId: string | null) => void;
}

export const AppointmentsView = ({ appointments, updateAppointmentStatus, updateAppointmentAssignment }: AppointmentsViewProps) => {
  const { t } = useLanguage();
  
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
        <div className="bg-green-700 text-white px-6 py-4">
          <h2 className="text-2xl font-bold">{t.admin.allAppointments}</h2>
          <p className="text-green-100">{t.admin.manageAllAppointments}</p>
        </div>
        <div className="p-6">
          <AppointmentsTab 
            appointments={appointments} 
            updateAppointmentStatus={updateAppointmentStatus}
            updateAppointmentAssignment={updateAppointmentAssignment}
          />
        </div>
      </div>
    </div>
  );
};
