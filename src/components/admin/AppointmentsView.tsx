
import { AppointmentsTab } from './AppointmentsTab';
import { Appointment } from '@/types/admin';

interface AppointmentsViewProps {
  appointments: Appointment[];
  updateAppointmentStatus: (id: string, status: string) => void;
}

export const AppointmentsView = ({ appointments, updateAppointmentStatus }: AppointmentsViewProps) => {
  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
        <div className="bg-green-700 text-white px-6 py-4">
          <h2 className="text-2xl font-bold">All Appointments</h2>
          <p className="text-green-100">Manage all service appointments</p>
        </div>
        <div className="p-6">
          <AppointmentsTab 
            appointments={appointments} 
            updateAppointmentStatus={updateAppointmentStatus} 
          />
        </div>
      </div>
    </div>
  );
};
