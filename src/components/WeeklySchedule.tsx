import React from 'react';
import { Clock, Calendar } from 'lucide-react';
import { useCustomHours } from '@/hooks/useCustomHours';

interface WeeklyScheduleProps {
  translations: any;
  primaryColor: string;
}

const WeeklySchedule: React.FC<WeeklyScheduleProps> = ({ translations, primaryColor }) => {
  const { getHoursForDate } = useCustomHours();
  const t = translations;

  // Get next 7 days
  const getNext7Days = () => {
    const days = [];
    const today = new Date();
    
    for (let i = 0; i < 7; i++) {
      const date = new Date(today);
      date.setDate(today.getDate() + i);
      days.push(date);
    }
    
    return days;
  };

  const formatDate = (date: Date) => {
    return date.toLocaleDateString('en-US', { 
      weekday: 'short', 
      month: 'short', 
      day: 'numeric' 
    });
  };

  const formatTime = (time: string | null) => {
    if (!time) return null;
    const [hours, minutes] = time.split(':');
    const hour24 = parseInt(hours);
    const hour12 = hour24 === 0 ? 12 : hour24 > 12 ? hour24 - 12 : hour24;
    const ampm = hour24 >= 12 ? 'PM' : 'AM';
    return `${hour12}:${minutes} ${ampm}`;
  };

  const days = getNext7Days();

  return (
    <div className="mb-8">
      <div className="text-center mb-6">
        <div className="flex items-center justify-center gap-2 mb-2">
          <Calendar className="h-5 w-5" style={{ color: primaryColor }} />
          <h3 className="text-xl font-semibold" style={{ color: primaryColor }}>
            {t.businessHours}
          </h3>
        </div>
        <p className="text-gray-600 text-sm">Next 7 Days</p>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-7 gap-2">
        {days.map((date, index) => {
          const dateStr = date.toISOString().split('T')[0];
          const hours = getHoursForDate(dateStr);
          const isToday = date.toDateString() === new Date().toDateString();
          
          return (
            <div 
              key={index}
              className={`p-3 rounded-lg border text-center ${
                isToday 
                  ? 'border-green-500 bg-green-50' 
                  : 'border-gray-200 bg-gray-50'
              }`}
            >
              <div className={`font-medium text-sm mb-1 ${
                isToday ? 'text-green-700' : 'text-gray-700'
              }`}>
                {formatDate(date)}
                {isToday && <span className="block text-xs text-green-600">Today</span>}
              </div>
              
              {hours.is_closed ? (
                <div className="text-red-600 text-xs font-medium">
                  Closed
                </div>
              ) : (
                <div className="text-gray-600 text-xs">
                  <div className="flex items-center justify-center gap-1 mb-1">
                    <Clock className="h-3 w-3" />
                    <span>Open</span>
                  </div>
                  <div>
                    {formatTime(hours.opening_time)} - {formatTime(hours.closing_time)}
                  </div>
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default WeeklySchedule;