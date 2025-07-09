
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ChevronLeft, User, Calendar, Clock, MessageSquare, Users, Repeat, TrendingUp } from 'lucide-react';
import { Appointment, ContactMessage } from '@/types/admin';

interface AnalyticsViewProps {
  appointments: Appointment[];
  contactMessages: ContactMessage[];
}

type DetailView = 'total' | 'new' | 'confirmed' | 'completed' | 'cancelled' | 'thisweek' | 'allmessages' | 'unreadmessages' | 'customers' | 'recurring' | null;

export const AnalyticsView = ({ appointments, contactMessages }: AnalyticsViewProps) => {
  const [detailView, setDetailView] = useState<DetailView>(null);
  
  const totalAppointments = appointments.length;
  const newAppointments = appointments.filter(apt => apt.status === 'new').length;
  const confirmedAppointments = appointments.filter(apt => apt.status === 'confirmed').length;
  const completedAppointments = appointments.filter(apt => apt.status === 'completed').length;
  const cancelledAppointments = appointments.filter(apt => apt.status === 'cancelled').length;
  
  const totalMessages = contactMessages.length;
  const unreadMessages = contactMessages.filter(msg => msg.status === 'new').length;
  
  const thisWeek = new Date();
  thisWeek.setDate(thisWeek.getDate() - 7);
  const recentAppointments = appointments.filter(apt => new Date(apt.created_at) > thisWeek);

  // Customer analytics
  const customerMap = new Map<string, {
    email: string;
    name: string;
    appointments: Appointment[];
    lastService: string;
    lastDate: string;
    totalSpent: number;
  }>();

  appointments.forEach(apt => {
    const key = apt.email.toLowerCase();
    const existing = customerMap.get(key);
    
    if (existing) {
      existing.appointments.push(apt);
      // Update last service if this appointment is more recent
      if (new Date(apt.created_at) > new Date(existing.lastDate)) {
        existing.lastService = apt.service;
        existing.lastDate = apt.created_at;
      }
    } else {
      customerMap.set(key, {
        email: apt.email,
        name: `${apt.first_name} ${apt.last_name}`,
        appointments: [apt],
        lastService: apt.service,
        lastDate: apt.created_at,
        totalSpent: 0 // Could be enhanced with pricing data
      });
    }
  });

  const allCustomers = Array.from(customerMap.values());
  const recurringCustomers = allCustomers.filter(customer => customer.appointments.length > 1);
  const totalCustomers = allCustomers.length;

  const getDetailedData = (type: DetailView) => {
    switch (type) {
      case 'total':
        return { type: 'appointments', data: appointments };
      case 'new':
        return { type: 'appointments', data: appointments.filter(apt => apt.status === 'new') };
      case 'confirmed':
        return { type: 'appointments', data: appointments.filter(apt => apt.status === 'confirmed') };
      case 'completed':
        return { type: 'appointments', data: appointments.filter(apt => apt.status === 'completed') };
      case 'cancelled':
        return { type: 'appointments', data: appointments.filter(apt => apt.status === 'cancelled') };
      case 'thisweek':
        return { type: 'appointments', data: recentAppointments };
      case 'allmessages':
        return { type: 'messages', data: contactMessages };
      case 'unreadmessages':
        return { type: 'messages', data: contactMessages.filter(msg => msg.status === 'new') };
      case 'customers':
        return { type: 'customers', data: allCustomers };
      case 'recurring':
        return { type: 'customers', data: recurringCustomers };
      default:
        return { type: 'appointments', data: [] };
    }
  };

  const getDetailTitle = (type: DetailView) => {
    switch (type) {
      case 'total':
        return 'All Appointments';
      case 'new':
        return 'New Appointments';
      case 'confirmed':
        return 'Confirmed Appointments';
      case 'completed':
        return 'Completed Appointments';
      case 'cancelled':
        return 'Cancelled Appointments';
      case 'thisweek':
        return 'This Week\'s Appointments';
      case 'allmessages':
        return 'All Messages';
      case 'unreadmessages':
        return 'Unread Messages';
      case 'customers':
        return 'All Customers';
      case 'recurring':
        return 'Recurring Customers';
      default:
        return '';
    }
  };

  const formatStatus = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
  };

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'new': 
      case 'pending': 
      case 'confirmed':
        return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'priority': 
        return 'bg-red-100 text-red-800 border-red-200';
      case 'completed': 
        return 'bg-green-100 text-green-800 border-green-200';
      case 'cancelled':
        return 'bg-gray-100 text-gray-800 border-gray-200';
      default: 
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  if (detailView) {
    const detailedData = getDetailedData(detailView);
    
    return (
      <div className="space-y-6">
        <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
          <div className="bg-green-700 text-white px-6 py-4 flex items-center gap-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setDetailView(null)}
              className="text-white hover:bg-green-600 p-2"
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <div>
              <h2 className="text-2xl font-bold">{getDetailTitle(detailView)}</h2>
              <p className="text-green-100">
                {detailedData.data.length} {
                  detailedData.type === 'appointments' ? 'appointment' : 
                  detailedData.type === 'customers' ? 'customer' : 'message'
                }{detailedData.data.length !== 1 ? 's' : ''}
              </p>
            </div>
          </div>
          <div className="p-6">
            {detailedData.data.length === 0 ? (
              <div className="text-center py-12 text-gray-500">
                <Calendar className="h-12 w-12 mx-auto mb-4 opacity-50" />
                <p>No {detailedData.type} found</p>
              </div>
            ) : (
              <div className="space-y-4">
                {detailedData.type === 'customers' ? (
                  (detailedData.data as any[]).map((customer) => (
                    <Card key={customer.email} className="border border-gray-200">
                      <CardContent className="p-4">
                        <div className="flex items-start justify-between mb-4">
                          <div className="flex items-center gap-3">
                            <Users className="h-5 w-5 text-gray-500" />
                            <div>
                              <h3 className="font-semibold text-lg text-gray-900">
                                {customer.name}
                              </h3>
                              <p className="text-sm text-gray-600">{customer.email}</p>
                            </div>
                          </div>
                          <div className={`px-3 py-1 rounded-full text-sm font-medium border ${
                            customer.appointments.length > 1 ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-blue-100 text-blue-800 border-blue-200'
                          }`}>
                            {customer.appointments.length} appointment{customer.appointments.length !== 1 ? 's' : ''}
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Last Service</p>
                            <p className="font-medium">{customer.lastService}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Last Visit</p>
                            <p className="font-medium">{new Date(customer.lastDate).toLocaleDateString()}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Total Visits</p>
                            <p className="font-medium">{customer.appointments.length}</p>
                          </div>
                        </div>
                        
                        <div className="mt-4">
                          <p className="text-xs text-gray-500 uppercase tracking-wide mb-2">Service History</p>
                          <div className="space-y-2">
                            {customer.appointments
                              .sort((a: Appointment, b: Appointment) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
                              .map((apt: Appointment) => (
                                <div key={apt.id} className="flex justify-between items-center py-2 px-3 bg-gray-50 rounded text-sm">
                                  <div>
                                    <span className="font-medium">{apt.service}</span>
                                    <span className="text-gray-500 ml-2">on {apt.preferred_date}</span>
                                  </div>
                                  <div className={`px-2 py-1 rounded text-xs ${getStatusColor(apt.status)}`}>
                                    {formatStatus(apt.status)}
                                  </div>
                                </div>
                              ))
                            }
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))
                ) : detailedData.type === 'appointments' ? (
                  (detailedData.data as Appointment[]).map((appointment) => (
                    <Card key={appointment.id} className="border border-gray-200">
                      <CardContent className="p-4">
                        <div className="flex items-start justify-between mb-3">
                          <div className="flex items-center gap-3">
                            <User className="h-5 w-5 text-gray-500" />
                            <div>
                              <h3 className="font-semibold text-lg text-gray-900">
                                {appointment.first_name} {appointment.last_name}
                              </h3>
                              <p className="text-sm text-gray-600">{appointment.email}</p>
                              {appointment.phone && (
                                <p className="text-sm text-gray-600">{appointment.phone}</p>
                              )}
                            </div>
                          </div>
                          <div className={`px-3 py-1 rounded-full text-sm font-medium border ${getStatusColor(appointment.status)}`}>
                            {formatStatus(appointment.status)}
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-3">
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Service</p>
                            <p className="font-medium">{appointment.service}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Date</p>
                            <p className="font-medium">{appointment.preferred_date}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Time</p>
                            <p className="font-medium">{appointment.preferred_time}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Created</p>
                            <p className="font-medium">{new Date(appointment.created_at).toLocaleDateString()}</p>
                          </div>
                        </div>
                        
                        {appointment.message && (
                          <div className="mt-3 p-3 bg-gray-50 rounded">
                            <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">Message</p>
                            <p className="text-sm text-gray-700 italic">"{appointment.message}"</p>
                          </div>
                        )}
                      </CardContent>
                    </Card>
                  ))
                ) : (
                  (detailedData.data as ContactMessage[]).map((message) => (
                    <Card key={message.id} className="border border-gray-200">
                      <CardContent className="p-4">
                        <div className="flex items-start justify-between mb-3">
                          <div className="flex items-center gap-3">
                            <MessageSquare className="h-5 w-5 text-gray-500" />
                            <div>
                              <h3 className="font-semibold text-lg text-gray-900">
                                {message.first_name} {message.last_name}
                              </h3>
                              <p className="text-sm text-gray-600">{message.email}</p>
                              {message.phone && (
                                <p className="text-sm text-gray-600">{message.phone}</p>
                              )}
                            </div>
                          </div>
                          <div className={`px-3 py-1 rounded-full text-sm font-medium border ${getStatusColor(message.status)}`}>
                            {formatStatus(message.status)}
                          </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 mb-3">
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Language</p>
                            <p className="font-medium">{message.language}</p>
                          </div>
                          <div>
                            <p className="text-xs text-gray-500 uppercase tracking-wide">Created</p>
                            <p className="font-medium">{new Date(message.created_at).toLocaleDateString()}</p>
                          </div>
                        </div>
                        
                        <div className="mt-3 p-3 bg-gray-50 rounded">
                          <p className="text-xs text-gray-500 uppercase tracking-wide mb-1">Message</p>
                          <p className="text-sm text-gray-700">"{message.message}"</p>
                        </div>
                      </CardContent>
                    </Card>
                  ))
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow-sm border-2 border-green-700">
        <div className="bg-green-700 text-white px-6 py-4">
          <h2 className="text-2xl font-bold">Analytics Dashboard</h2>
          <p className="text-green-100">Overview of your business metrics</p>
        </div>
        <div className="p-6">
          {/* Top row: Total Appointments & This Week */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <button
              onClick={() => setDetailView('total')}
              className="bg-gray-50 p-4 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-black">Total Appointments</h3>
              <p className="text-2xl font-bold text-black">{totalAppointments}</p>
            </button>
            
            <button
              onClick={() => setDetailView('thisweek')}
              className="bg-purple-50 p-4 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-purple-800">This Week</h3>
              <p className="text-2xl font-bold text-purple-900">{recentAppointments.length}</p>
            </button>
          </div>

          {/* Bottom row: Status-based appointments */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <button
              onClick={() => setDetailView('new')}
              className="bg-yellow-50 p-4 rounded-lg border border-yellow-200 hover:bg-yellow-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-yellow-800">New</h3>
              <p className="text-2xl font-bold text-yellow-900">{newAppointments}</p>
            </button>

            <button
              onClick={() => setDetailView('confirmed')}
              className="bg-blue-50 p-4 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-blue-800">Confirmed</h3>
              <p className="text-2xl font-bold text-blue-900">{confirmedAppointments}</p>
            </button>
            
            <button
              onClick={() => setDetailView('completed')}
              className="bg-green-50 p-4 rounded-lg border border-green-200 hover:bg-green-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-green-800">Completed</h3>
              <p className="text-2xl font-bold text-green-900">{completedAppointments}</p>
            </button>
            
            <button
              onClick={() => setDetailView('cancelled')}
              className="bg-red-50 p-4 rounded-lg border border-red-200 hover:bg-red-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-red-800">Cancelled</h3>
              <p className="text-2xl font-bold text-red-900">{cancelledAppointments}</p>
            </button>
          </div>
          
          {/* Customer Analytics Section */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <button
              onClick={() => setDetailView('customers')}
              className="bg-indigo-50 p-4 rounded-lg border border-indigo-200 hover:bg-indigo-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-indigo-800 flex items-center gap-2 mb-2">
                <Users className="h-5 w-5" />
                All Customers
              </h3>
              <p className="text-2xl font-bold text-indigo-900">{totalCustomers}</p>
              <p className="text-sm text-indigo-600">Unique customers</p>
            </button>

            <button
              onClick={() => setDetailView('recurring')}
              className="bg-purple-50 p-4 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors text-left"
            >
              <h3 className="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                <Repeat className="h-5 w-5" />
                Recurring Customers
              </h3>
              <p className="text-2xl font-bold text-purple-900">{recurringCustomers.length}</p>
              <p className="text-sm text-purple-600">{Math.round((recurringCustomers.length / totalCustomers) * 100) || 0}% of all customers</p>
            </button>

            <div className="bg-orange-50 p-4 rounded-lg border border-orange-200">
              <h3 className="font-semibold text-orange-800 flex items-center gap-2 mb-2">
                <TrendingUp className="h-5 w-5" />
                Customer Retention
              </h3>
              <p className="text-2xl font-bold text-orange-900">
                {recurringCustomers.length > 0 ? 
                  Math.round(recurringCustomers.reduce((acc, customer) => acc + customer.appointments.length, 0) / recurringCustomers.length * 10) / 10 : 0
                }
              </p>
              <p className="text-sm text-orange-600">Avg visits per returning customer</p>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <h3 className="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <MessageSquare className="h-5 w-5" />
                Message Statistics
              </h3>
              <div className="space-y-2">
                <button
                  onClick={() => setDetailView('allmessages')}
                  className="w-full flex justify-between hover:bg-gray-100 p-2 rounded transition-colors"
                >
                  <span>Total Messages:</span>
                  <span className="font-bold">{totalMessages}</span>
                </button>
                <button
                  onClick={() => setDetailView('unreadmessages')}
                  className="w-full flex justify-between hover:bg-gray-100 p-2 rounded transition-colors"
                >
                  <span>Unread Messages:</span>
                  <span className="font-bold text-red-600">{unreadMessages}</span>
                </button>
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
