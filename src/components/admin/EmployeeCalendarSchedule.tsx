import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Calendar, Plus, Edit3, Trash2, Clock, Save, X } from 'lucide-react';
import { useEmployeeSchedules, EmployeeWithSchedule } from '@/hooks/useEmployeeSchedules';
import { format, addDays, startOfWeek, endOfWeek, isSameDay } from 'date-fns';

interface EmployeeCalendarScheduleProps {
  employee: EmployeeWithSchedule;
}

interface EditingSchedule {
  date: string;
  startTime: string;
  endTime: string;
}

export const EmployeeCalendarSchedule = ({ employee }: EmployeeCalendarScheduleProps) => {
  const { saveEmployeeSchedule, deleteEmployeeSchedule } = useEmployeeSchedules();
  const [currentDate, setCurrentDate] = useState(new Date());
  const [editingSchedule, setEditingSchedule] = useState<EditingSchedule | null>(null);

  // Get the week range
  const weekStart = startOfWeek(currentDate, { weekStartsOn: 0 }); // Sunday
  const weekEnd = endOfWeek(currentDate, { weekStartsOn: 0 }); // Saturday
  
  // Generate days for the current week
  const weekDays = [];
  for (let i = 0; i < 7; i++) {
    weekDays.push(addDays(weekStart, i));
  }

  const getScheduleForDate = (date: Date) => {
    const dateStr = format(date, 'yyyy-MM-dd');
    return employee.schedules.find(schedule => schedule.schedule_date === dateStr);
  };

  const handleAddSchedule = (date: Date) => {
    const dateStr = format(date, 'yyyy-MM-dd');
    setEditingSchedule({
      date: dateStr,
      startTime: '08:00',
      endTime: '17:00'
    });
  };

  const handleEditSchedule = (date: Date) => {
    const schedule = getScheduleForDate(date);
    if (schedule) {
      setEditingSchedule({
        date: schedule.schedule_date,
        startTime: schedule.start_time,
        endTime: schedule.end_time
      });
    }
  };

  const handleSaveSchedule = async () => {
    if (!editingSchedule) return;

    await saveEmployeeSchedule(
      employee.id,
      editingSchedule.date,
      editingSchedule.startTime,
      editingSchedule.endTime
    );
    
    setEditingSchedule(null);
  };

  const handleDeleteSchedule = async (date: Date) => {
    const dateStr = format(date, 'yyyy-MM-dd');
    await deleteEmployeeSchedule(employee.id, dateStr);
  };

  const navigateWeek = (direction: 'prev' | 'next') => {
    const days = direction === 'prev' ? -7 : 7;
    setCurrentDate(prev => addDays(prev, days));
  };

  const isEditing = (date: Date) => {
    if (!editingSchedule) return false;
    return editingSchedule.date === format(date, 'yyyy-MM-dd');
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle className="flex items-center gap-2">
            <Calendar className="h-5 w-5" />
            {employee.name} - Weekly Schedule
          </CardTitle>
          <div className="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm" 
              onClick={() => navigateWeek('prev')}
            >
              ← Previous
            </Button>
            <span className="font-medium">
              {format(weekStart, 'MMM d')} - {format(weekEnd, 'MMM d, yyyy')}
            </span>
            <Button 
              variant="outline" 
              size="sm" 
              onClick={() => navigateWeek('next')}
            >
              Next →
            </Button>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-7 gap-2">
          {weekDays.map((day, index) => {
            const schedule = getScheduleForDate(day);
            const dayName = format(day, 'EEE');
            const dayNumber = format(day, 'd');
            const isToday = isSameDay(day, new Date());
            const editing = isEditing(day);

            return (
              <div 
                key={index} 
                className={`border rounded-lg p-3 min-h-[120px] ${
                  isToday ? 'border-primary bg-primary/5' : 'border-border'
                }`}
              >
                <div className="text-center mb-2">
                  <div className="font-medium text-sm">{dayName}</div>
                  <div className={`text-lg font-bold ${isToday ? 'text-primary' : ''}`}>
                    {dayNumber}
                  </div>
                </div>

                {editing ? (
                  <div className="space-y-2">
                    <Input
                      type="time"
                      value={editingSchedule?.startTime || ''}
                      onChange={(e) => setEditingSchedule(prev => 
                        prev ? { ...prev, startTime: e.target.value } : null
                      )}
                      className="text-xs"
                    />
                    <Input
                      type="time"
                      value={editingSchedule?.endTime || ''}
                      onChange={(e) => setEditingSchedule(prev => 
                        prev ? { ...prev, endTime: e.target.value } : null
                      )}
                      className="text-xs"
                    />
                    <div className="flex gap-1">
                      <Button
                        size="sm"
                        onClick={handleSaveSchedule}
                        className="flex-1 h-7 text-xs"
                      >
                        <Save className="h-3 w-3" />
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => setEditingSchedule(null)}
                        className="h-7 px-2"
                      >
                        <X className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>
                ) : schedule ? (
                  <div className="space-y-2">
                    <Badge variant="secondary" className="w-full justify-center text-xs">
                      <Clock className="h-3 w-3 mr-1" />
                      Scheduled
                    </Badge>
                    <div className="text-center text-xs text-muted-foreground">
                      {schedule.start_time} - {schedule.end_time}
                    </div>
                    <div className="flex gap-1">
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleEditSchedule(day)}
                        className="flex-1 h-7 text-xs"
                      >
                        <Edit3 className="h-3 w-3" />
                      </Button>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleDeleteSchedule(day)}
                        className="h-7 px-2 text-red-600 hover:text-red-700"
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>
                ) : (
                  <div className="text-center">
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => handleAddSchedule(day)}
                      className="w-full h-7 text-xs"
                    >
                      <Plus className="h-3 w-3 mr-1" />
                      Add Schedule
                    </Button>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
};