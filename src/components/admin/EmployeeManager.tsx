import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Plus, Edit2, Save, X, Calendar } from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { useEmployees, Employee } from '@/hooks/useEmployees';
import { useEmployeeSchedules } from '@/hooks/useEmployeeSchedules';
import { useLanguage } from '@/hooks/useLanguage';
import { EmployeeCalendarSchedule } from './EmployeeCalendarSchedule';
import { EmployeeScheduleAlert } from './EmployeeScheduleAlert';


export const EmployeeManager = () => {
  const { t } = useLanguage();
  const { toast } = useToast();
  const { employees, loading, refetch } = useEmployees();
  const { employeesWithSchedules, loading: schedulesLoading } = useEmployeeSchedules();
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editingData, setEditingData] = useState<Partial<Employee>>({});
  const [newEmployee, setNewEmployee] = useState({ name: '', email: '', phone: '', role: 'Worker' });
  const [showAddForm, setShowAddForm] = useState(false);
  const [expandedSchedule, setExpandedSchedule] = useState<string | null>(null);
  const [scheduleDate, setScheduleDate] = useState<string | null>(null);

  const handleAddEmployee = async () => {
    if (!newEmployee.name.trim()) return;

    try {
      const { error } = await supabase
        .from('oregon_tires_employees')
        .insert([{
          name: newEmployee.name,
          email: newEmployee.email || null,
          phone: newEmployee.phone || null,
          role: newEmployee.role
        }]);

      if (error) throw error;

      setNewEmployee({ name: '', email: '', phone: '', role: 'Worker' });
      setShowAddForm(false);
      
      toast({
        title: t.admin.success,
        description: t.admin.employeeAddedSuccess,
      });
    } catch (error) {
      console.error('Error adding employee:', error);
      toast({
        title: t.admin.error,
        description: t.admin.failedToAddEmployee,
        variant: "destructive",
      });
    }
  };

  const handleUpdateEmployee = async (id: string, updates: Partial<Employee>) => {
    try {
      const { error } = await supabase
        .from('oregon_tires_employees')
        .update(updates)
        .eq('id', id);

      if (error) throw error;

      setEditingId(null);
      setEditingData({});

      toast({
        title: t.admin.success,
        description: t.admin.employeeUpdatedSuccess,
      });
    } catch (error) {
      console.error('Error updating employee:', error);
      toast({
        title: t.admin.error,
        description: t.admin.failedToUpdateEmployee,
        variant: "destructive",
      });
    }
  };

  const startEditing = (employee: Employee) => {
    setEditingId(employee.id);
    setEditingData({
      name: employee.name,
      email: employee.email,
      phone: employee.phone,
      role: employee.role
    });
  };

  const cancelEditing = () => {
    setEditingId(null);
    setEditingData({});
  };

  if (loading || schedulesLoading) {
    return <div className="text-green-700">{t.admin.loadingEmployees}</div>;
  }

  return (
    <Card className="border-2 border-green-700">
      <CardHeader className="bg-green-700 text-white">
        <div className="flex items-center justify-between">
          <CardTitle>{t.admin.employeeManagement}</CardTitle>
          <Button 
            variant="secondary" 
            size="sm"
            onClick={() => setShowAddForm(!showAddForm)}
          >
            <Plus className="h-4 w-4 mr-1" />
            {t.admin.addEmployee}
          </Button>
        </div>
      </CardHeader>
      <CardContent className="p-4">
        {showAddForm && (
          <div className="mb-6 p-4 border rounded-lg bg-gray-50">
            <h4 className="font-medium mb-3">{t.admin.addNewEmployee}</h4>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-3">
              <div>
                <Label htmlFor="name">{t.admin.name} *</Label>
                <Input
                  id="name"
                  value={newEmployee.name}
                  onChange={(e) => setNewEmployee(prev => ({ ...prev, name: e.target.value }))}
                  placeholder="Employee name"
                />
              </div>
              <div>
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  value={newEmployee.email}
                  onChange={(e) => setNewEmployee(prev => ({ ...prev, email: e.target.value }))}
                  placeholder="employee@email.com"
                />
              </div>
              <div>
                <Label htmlFor="phone">Phone</Label>
                <Input
                  id="phone"
                  value={newEmployee.phone}
                  onChange={(e) => setNewEmployee(prev => ({ ...prev, phone: e.target.value }))}
                  placeholder="(555) 123-4567"
                />
              </div>
              <div>
                <Label htmlFor="role">Role</Label>
                <select
                  id="role"
                  className="w-full h-10 px-3 py-2 border border-input bg-background rounded-md text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:outline-none"
                  value={newEmployee.role}
                  onChange={(e) => setNewEmployee(prev => ({ ...prev, role: e.target.value }))}
                >
                  <option value="Worker">Worker</option>
                  <option value="Manager">Manager</option>
                </select>
              </div>
            </div>
            <div className="flex gap-2 mt-3">
              <Button onClick={handleAddEmployee} size="sm">
                <Save className="h-4 w-4 mr-1" />
                Save
              </Button>
              <Button 
                variant="outline" 
                size="sm"
                onClick={() => {
                  setShowAddForm(false);
                  setNewEmployee({ name: '', email: '', phone: '', role: 'Worker' });
                }}
              >
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>
          </div>
        )}

        <div className="space-y-3">
          {employees.map((employee) => {
            const employeeWithSchedule = employeesWithSchedules.find(emp => emp.id === employee.id);
            
            return (
              <div 
                key={employee.id} 
                className="border rounded-lg bg-white p-3"
              >
                {editingId === employee.id ? (
                <div className="grid grid-cols-1 md:grid-cols-5 gap-3 items-center">
                  <Input
                    value={editingData.name || ''}
                    onChange={(e) => setEditingData(prev => ({ ...prev, name: e.target.value }))}
                  />
                  <Input
                    value={editingData.email || ''}
                    onChange={(e) => setEditingData(prev => ({ ...prev, email: e.target.value }))}
                    placeholder="Email"
                  />
                  <Input
                    value={editingData.phone || ''}
                    onChange={(e) => setEditingData(prev => ({ ...prev, phone: e.target.value }))}
                    placeholder="Phone"
                  />
                  <select
                    className="w-full h-10 px-3 py-2 border border-input bg-background rounded-md text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:outline-none"
                    value={editingData.role || 'Worker'}
                    onChange={(e) => setEditingData(prev => ({ ...prev, role: e.target.value }))}
                  >
                    <option value="Worker">Worker</option>
                    <option value="Manager">Manager</option>
                  </select>
                  <div className="flex gap-2">
                    <Button 
                      size="sm" 
                      onClick={() => handleUpdateEmployee(employee.id, editingData)}
                    >
                      <Save className="h-4 w-4" />
                    </Button>
                    <Button 
                      variant="outline" 
                      size="sm"
                      onClick={cancelEditing}
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              ) : (
                <>
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      <div className="flex items-center gap-2">
                        <span className="font-medium">{employee.name}</span>
                        <span className={`text-xs px-2 py-1 rounded-full ${
                          employee.role === 'Manager' 
                            ? 'bg-blue-100 text-blue-800' 
                            : 'bg-gray-100 text-gray-800'
                        }`}>
                          {employee.role}
                        </span>
                      </div>
                      <div className="text-sm text-gray-600">
                        {employee.email && <span>{employee.email}</span>}
                        {employee.email && employee.phone && <span> • </span>}
                        {employee.phone && <span>{employee.phone}</span>}
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <Button 
                        variant="outline" 
                        size="sm"
                        onClick={() => setExpandedSchedule(
                          expandedSchedule === employee.id ? null : employee.id
                        )}
                      >
                        <Calendar className="h-4 w-4 mr-1" />
                        Schedule
                      </Button>
                      <Button 
                        variant="outline" 
                        size="sm"
                        onClick={() => startEditing(employee)}
                      >
                        <Edit2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                  
                  {/* Schedule conflict alert */}
                  {employeeWithSchedule && (
                    <EmployeeScheduleAlert 
                      employee={employeeWithSchedule} 
                      onAppointmentClick={(employeeId, date) => {
                        setExpandedSchedule(employeeId);
                        // Navigate to the correct date in the calendar
                        setScheduleDate(date);
                        // Scroll to the schedule section after a brief delay
                        setTimeout(() => {
                          const element = document.getElementById(`schedule-${employeeId}`);
                          element?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 100);
                      }}
                    />
                  )}
                  
                  {/* Employee schedule management */}
                  {expandedSchedule === employee.id && employeeWithSchedule && (
                    <div id={`schedule-${employee.id}`}>
                      <EmployeeCalendarSchedule 
                        employee={employeeWithSchedule} 
                        initialDate={scheduleDate}
                      />
                    </div>
                  )}
                </>
              )}
            </div>
            );
          })}
        </div>

        {employees.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            {t.admin.noEmployeesFound}
          </div>
        )}
      </CardContent>
    </Card>
  );
};