import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Plus, Edit2, Save, X, Calendar, Shield, ShieldOff, Power, PowerOff, CalendarDays, Clock, Settings } from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { useEmployees, Employee } from '@/hooks/useEmployees';
import { useEmployeeSchedules } from '@/hooks/useEmployeeSchedules';
import { useEmployeeAppointments } from '@/hooks/useEmployeeAppointments';
import { useLanguage } from '@/hooks/useLanguage';
import { EmployeeCalendarSchedule } from './EmployeeCalendarSchedule';
import { EmployeeScheduleAlert } from './EmployeeScheduleAlert';
import { EmployeeEditDialog } from './EmployeeEditDialog';


export const EmployeeManager = () => {
  const { t } = useLanguage();
  const { toast } = useToast();
  const { employees, loading, refetch } = useEmployees();
  const { employeesWithSchedules, loading: schedulesLoading } = useEmployeeSchedules();
  const { getEmployeeAppointmentSummary, loading: appointmentsLoading } = useEmployeeAppointments();
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editingData, setEditingData] = useState<Partial<Employee>>({});
  const [newEmployee, setNewEmployee] = useState({ name: '', email: '', phone: '', role: 'Employee' });
  const [showAddForm, setShowAddForm] = useState(false);
  const [expandedSchedule, setExpandedSchedule] = useState<string | null>(null);
  const [scheduleDate, setScheduleDate] = useState<string | null>(null);
  const [editDialogEmployee, setEditDialogEmployee] = useState<Employee | null>(null);
  const [showEditDialog, setShowEditDialog] = useState(false);

  // Separate active and inactive employees
  const activeEmployees = employees.filter(emp => emp.is_active);
  const inactiveEmployees = employees.filter(emp => !emp.is_active);

  const handleAddEmployee = async () => {
    if (!newEmployee.name.trim()) return;

    try {
      const { error } = await supabase
        .from('oretir_employees')
        .insert([{
          name: newEmployee.name,
          email: newEmployee.email || null,
          phone: newEmployee.phone || null,
          role: newEmployee.role
        }]);

      if (error) throw error;

      setNewEmployee({ name: '', email: '', phone: '', role: 'Employee' });
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
        .from('oretir_employees')
        .update(updates)
        .eq('id', id);

      if (error) throw error;

      setEditingId(null);
      setEditingData({});

      // Manually refresh both hooks to ensure immediate update
      refetch();

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

  const handleMakeAdmin = async (employee: Employee) => {
    if (!employee.email) {
      toast({
        title: t.admin.error,
        description: "Employee must have an email address to become an admin",
        variant: "destructive",
      });
      return;
    }

    try {
      const { error } = await supabase.rpc('set_admin_by_email', {
        user_email: employee.email
      });

      if (error) throw error;

      toast({
        title: "Success",
        description: `${employee.name} has been granted admin access`,
      });
    } catch (error) {
      console.error('Error granting admin access:', error);
      toast({
        title: "Error", 
        description: `${employee.name} (${employee.email}) needs to create an account first. Ask them to sign up on the login page, then try again.`,
        variant: "destructive",
      });
    }
  };

  const handleToggleEmployeeStatus = async (employee: Employee) => {
    try {
      const { error } = await supabase
        .from('oretir_employees')
        .update({ is_active: !employee.is_active })
        .eq('id', employee.id);

      if (error) throw error;

      refetch();

      toast({
        title: "Success",
        description: `${employee.name} has been ${!employee.is_active ? 'activated' : 'deactivated'}`,
      });
    } catch (error) {
      console.error('Error updating employee status:', error);
      toast({
        title: "Error",
        description: "Failed to update employee status",
        variant: "destructive",
      });
    }
  };

  const handleOpenEditDialog = (employee: Employee) => {
    setEditDialogEmployee(employee);
    setShowEditDialog(true);
  };

  const handleEmployeeUpdated = () => {
    refetch();
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

  if (loading || schedulesLoading || appointmentsLoading) {
    return <div className="text-green-700">{t.admin.loadingEmployees}</div>;
  }

  const renderEmployeeCard = (employee: Employee, isActive: boolean) => {
    const employeeWithSchedule = employeesWithSchedules.find(emp => emp.id === employee.id);
    const appointmentSummary = getEmployeeAppointmentSummary(employee.id);
    
    return (
      <div 
        key={employee.id} 
        className={`border rounded-lg p-3 ${isActive ? 'bg-white' : 'bg-gray-50'}`}
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
              value={editingData.role || 'Employee'}
              onChange={(e) => setEditingData(prev => ({ ...prev, role: e.target.value }))}
            >
              <option value="Employee">Employee</option>
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
                  {!isActive && (
                    <Badge variant="secondary" className="text-xs">
                      Inactive
                    </Badge>
                  )}
                </div>
                <div className="text-sm text-gray-600">
                  {employee.email && <span>{employee.email}</span>}
                  {employee.email && employee.phone && <span> • </span>}
                  {employee.phone && <span>{employee.phone}</span>}
                </div>
                
                {/* Appointment Summary */}
                {isActive && appointmentSummary && (
                  <div className="mt-2 p-2 bg-blue-50 rounded text-sm">
                    <div className="flex items-center gap-4">
                      <div className="flex items-center gap-1">
                        <CalendarDays className="h-4 w-4 text-blue-600" />
                        <span className="text-blue-800 font-medium">
                          {appointmentSummary.upcoming_count} upcoming
                        </span>
                      </div>
                      {appointmentSummary.next_appointment_date && (
                        <div className="flex items-center gap-1">
                          <Clock className="h-4 w-4 text-blue-600" />
                          <span className="text-blue-700">
                            Next: {new Date(appointmentSummary.next_appointment_date).toLocaleDateString()}
                            {appointmentSummary.next_appointment_service && 
                              ` (${appointmentSummary.next_appointment_service})`
                            }
                          </span>
                        </div>
                      )}
                    </div>
                  </div>
                )}
              </div>
              <div className="flex items-center gap-3">
                <Button 
                  variant={isActive ? "outline" : "default"}
                  size="sm"
                  onClick={() => handleToggleEmployeeStatus(employee)}
                  className={isActive ? "text-red-600 hover:text-red-700" : "text-green-600 hover:text-green-700"}
                >
                  {isActive ? (
                    <>
                      <PowerOff className="h-4 w-4 mr-1" />
                      Deactivate
                    </>
                  ) : (
                    <>
                      <Power className="h-4 w-4 mr-1" />
                      Activate
                    </>
                  )}
                </Button>
                {isActive && employee.email && (
                  <Button 
                    variant="outline" 
                    size="sm"
                    onClick={() => handleMakeAdmin(employee)}
                    className="text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                  >
                    <Shield className="h-4 w-4 mr-1" />
                    Make Admin
                  </Button>
                )}
                {isActive && (
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
                )}
                <Button 
                  variant="outline" 
                  size="sm"
                  onClick={() => handleOpenEditDialog(employee)}
                  className="text-gray-600 hover:text-gray-700"
                >
                  <Settings className="h-4 w-4 mr-1" />
                  Manage
                </Button>
              </div>
            </div>
            
            {/* Schedule conflict alert */}
            {isActive && employeeWithSchedule && (
              <EmployeeScheduleAlert 
                employee={employeeWithSchedule} 
                onAppointmentClick={(employeeId, date) => {
                  console.log('Appointment clicked:', { employeeId, date });
                  setExpandedSchedule(employeeId);
                  setScheduleDate(date);
                  setTimeout(() => {
                    const element = document.getElementById(`schedule-${employeeId}`);
                    element?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                  }, 100);
                }}
              />
            )}
            
            {/* Employee schedule management */}
            {isActive && expandedSchedule === employee.id && employeeWithSchedule && (
              <div id={`schedule-${employee.id}`}>
                <EmployeeCalendarSchedule 
                  key={`${employee.id}-${scheduleDate}`}
                  employee={employeeWithSchedule} 
                  initialDate={scheduleDate}
                />
              </div>
            )}
          </>
        )}
      </div>
    );
  };

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
                    <option value="Employee">Employee</option>
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
                  setNewEmployee({ name: '', email: '', phone: '', role: 'Employee' });
                }}
              >
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>
          </div>
        )}

        {/* Active Employees Section */}
        <div className="space-y-4">
          <div className="flex items-center gap-2">
            <h3 className="text-lg font-semibold text-green-700">Active Team Members</h3>
            <Badge variant="outline" className="text-green-700">
              {activeEmployees.length}
            </Badge>
          </div>
          
          <div className="space-y-3">
            {activeEmployees.map((employee) => renderEmployeeCard(employee, true))}
          </div>
          
          {activeEmployees.length === 0 && (
            <div className="text-center py-4 text-gray-500 border rounded-lg bg-gray-50">
              No active employees found
            </div>
          )}
        </div>

        {/* Inactive Employees Section */}
        {inactiveEmployees.length > 0 && (
          <>
            <Separator className="my-6" />
            <div className="space-y-4">
              <div className="flex items-center gap-2">
                <h3 className="text-lg font-semibold text-gray-600">Past Employees</h3>
                <Badge variant="secondary">
                  {inactiveEmployees.length}
                </Badge>
              </div>
              
              <div className="space-y-3">
                {inactiveEmployees.map((employee) => renderEmployeeCard(employee, false))}
              </div>
            </div>
          </>
        )}

        {employees.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            {t.admin.noEmployeesFound}
          </div>
        )}
      </CardContent>

      {/* Employee Edit Dialog */}
      <EmployeeEditDialog
        employee={editDialogEmployee}
        open={showEditDialog}
        onOpenChange={setShowEditDialog}
        onEmployeeUpdated={handleEmployeeUpdated}
      />
    </Card>
  );
};