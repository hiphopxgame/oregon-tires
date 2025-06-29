
import { Appointment } from '@/types/admin';

interface DailySummaryProps {
  appointments: Appointment[];
}

export const DailySummary = ({ appointments }: DailySummaryProps) => {
  return (
    <div className="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div>
          <div className="text-2xl font-bold text-black">
            {appointments.length}
          </div>
          <div className="text-sm text-gray-600">Total Appointments</div>
        </div>
        <div>
          <div className="text-2xl font-bold text-blue-600">
            {appointments.filter(apt => apt.status === 'confirmed').length}
          </div>
          <div className="text-sm text-gray-600">Confirmed</div>
        </div>
        <div>
          <div className="text-2xl font-bold text-green-600">
            {appointments.filter(apt => apt.status === 'completed').length}
          </div>
          <div className="text-sm text-gray-600">Completed</div>
        </div>
        <div>
          <div className="text-2xl font-bold text-red-600">
            {appointments.filter(apt => apt.status === 'cancelled').length}
          </div>
          <div className="text-sm text-gray-600">Cancelled</div>
        </div>
      </div>
    </div>
  );
};
