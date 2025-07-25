import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Trash2, Plus, Calendar, Clock } from 'lucide-react';
import { useEmployeeSchedules, EmployeeWithSchedule } from '@/hooks/useEmployeeSchedules';
import { useLanguage } from '@/hooks/useLanguage';

const DAYS_OF_WEEK = [
  { value: 0, label: 'Sunday', short: 'Sun' },
  { value: 1, label: 'Monday', short: 'Mon' },
  { value: 2, label: 'Tuesday', short: 'Tue' },
  { value: 3, label: 'Wednesday', short: 'Wed' },
  { value: 4, label: 'Thursday', short: 'Thu' },
  { value: 5, label: 'Friday', short: 'Fri' },
  { value: 6, label: 'Saturday', short: 'Sat' }
];

interface EmployeeScheduleManagerProps {
  employee: EmployeeWithSchedule;
}

export const EmployeeScheduleManager = ({ employee }: EmployeeScheduleManagerProps) => {
  const { t } = useLanguage();
  const { saveEmployeeSchedule, deleteEmployeeSchedule } = useEmployeeSchedules();
  const [editingDay, setEditingDay] = useState<number | null>(null);
  const [startTime, setStartTime] = useState('09:00');
  const [endTime, setEndTime] = useState('17:00');

  const handleSaveSchedule = async (dayOfWeek: number) => {
    if (!startTime || !endTime) return;
    
    await saveEmployeeSchedule(employee.id, dayOfWeek, startTime, endTime);
    setEditingDay(null);
    setStartTime('09:00');
    setEndTime('17:00');
  };

  const handleDeleteSchedule = async (dayOfWeek: number) => {
    await deleteEmployeeSchedule(employee.id, dayOfWeek);
  };

  const getScheduleForDay = (dayOfWeek: number) => {
    return employee.schedules.find(schedule => schedule.day_of_week === dayOfWeek);
  };

  const startEditing = (dayOfWeek: number) => {
    const existingSchedule = getScheduleForDay(dayOfWeek);
    if (existingSchedule) {
      setStartTime(existingSchedule.start_time);
      setEndTime(existingSchedule.end_time);
    }
    setEditingDay(dayOfWeek);
  };

  return (
    <Card className="mt-4">
      <CardHeader className="pb-3">
        <CardTitle className="flex items-center gap-2 text-lg">
          <Calendar className="h-5 w-5" />
          Weekly Schedule for {employee.name}
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        {DAYS_OF_WEEK.map((day) => {
          const schedule = getScheduleForDay(day.value);
          const isEditing = editingDay === day.value;

          return (
            <div key={day.value} className="flex items-center justify-between p-3 border rounded-lg">
              <div className="flex items-center gap-3">
                <span className="font-medium w-20">{day.short}</span>
                {schedule && !isEditing ? (
                  <div className="flex items-center gap-2">
                    <Clock className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm">
                      {schedule.start_time} - {schedule.end_time}
                    </span>
                  </div>
                ) : isEditing ? (
                  <div className="flex items-center gap-2">
                    <Input
                      type="time"
                      value={startTime}
                      onChange={(e) => setStartTime(e.target.value)}
                      className="w-32"
                    />
                    <span>to</span>
                    <Input
                      type="time"
                      value={endTime}
                      onChange={(e) => setEndTime(e.target.value)}
                      className="w-32"
                    />
                  </div>
                ) : (
                  <span className="text-muted-foreground text-sm">Not scheduled</span>
                )}
              </div>

              <div className="flex items-center gap-2">
                {isEditing ? (
                  <>
                    <Button
                      size="sm"
                      onClick={() => handleSaveSchedule(day.value)}
                      disabled={!startTime || !endTime}
                    >
                      Save
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => setEditingDay(null)}
                    >
                      Cancel
                    </Button>
                  </>
                ) : (
                  <>
                    <Button
                      size="sm"
                      variant="outline"
                      onClick={() => startEditing(day.value)}
                    >
                      {schedule ? 'Edit' : 'Add'}
                    </Button>
                    {schedule && (
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => handleDeleteSchedule(day.value)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    )}
                  </>
                )}
              </div>
            </div>
          );
        })}
      </CardContent>
    </Card>
  );
};