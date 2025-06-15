
import { Appointment, ContactMessage } from '@/types/admin';

interface AnalyticsViewProps {
  appointments: Appointment[];
  contactMessages: ContactMessage[];
}

export const AnalyticsView = ({ appointments, contactMessages }: AnalyticsViewProps) => {
  const totalAppointments = appointments.length;
  const pendingAppointments = appointments.filter(apt => apt.status === 'new' || apt.status === 'pending').length;
  const completedAppointments = appointments.filter(apt => apt.status === 'completed').length;
  
  const totalMessages = contactMessages.length;
  const unreadMessages = contactMessages.filter(msg => msg.status === 'new').length;
  
  const thisWeek = new Date();
  thisWeek.setDate(thisWeek.getDate() - 7);
  const recentAppointments = appointments.filter(apt => new Date(apt.created_at) > thisWeek).length;

  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
        <div className="bg-green-700 text-white px-6 py-4">
          <h2 className="text-2xl font-bold">Analytics Dashboard</h2>
          <p className="text-green-100">Overview of your business metrics</p>
        </div>
        <div className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <h3 className="font-semibold text-blue-800">Total Appointments</h3>
              <p className="text-2xl font-bold text-blue-900">{totalAppointments}</p>
            </div>
            
            <div className="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
              <h3 className="font-semibold text-yellow-800">Pending Appointments</h3>
              <p className="text-2xl font-bold text-yellow-900">{pendingAppointments}</p>
            </div>
            
            <div className="bg-green-50 p-4 rounded-lg border border-green-200">
              <h3 className="font-semibold text-green-800">Completed</h3>
              <p className="text-2xl font-bold text-green-900">{completedAppointments}</p>
            </div>
            
            <div className="bg-purple-50 p-4 rounded-lg border border-purple-200">
              <h3 className="font-semibold text-purple-800">This Week</h3>
              <p className="text-2xl font-bold text-purple-900">{recentAppointments}</p>
            </div>
          </div>
          
          <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <h3 className="font-semibold text-gray-800 mb-4">Message Statistics</h3>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span>Total Messages:</span>
                  <span className="font-bold">{totalMessages}</span>
                </div>
                <div className="flex justify-between">
                  <span>Unread Messages:</span>
                  <span className="font-bold text-red-600">{unreadMessages}</span>
                </div>
              </div>
            </div>
            
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <h3 className="font-semibold text-gray-800 mb-4">Popular Services</h3>
              <div className="space-y-2">
                {Object.entries(
                  appointments.reduce((acc, apt) => {
                    acc[apt.service] = (acc[apt.service] || 0) + 1;
                    return acc;
                  }, {} as Record<string, number>)
                )
                .sort(([,a], [,b]) => b - a)
                .slice(0, 3)
                .map(([service, count]) => (
                  <div key={service} className="flex justify-between">
                    <span>{service}:</span>
                    <span className="font-bold">{count}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
