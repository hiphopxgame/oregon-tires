import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Plus, Edit2, Save, X } from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { useEmployees, Employee } from '@/hooks/useEmployees';

export const EmployeeManager = () => {
  const { toast } = useToast();
  const { employees, loading, refetch } = useEmployees();
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editingData, setEditingData] = useState<Partial<Employee>>({});
  const [newEmployee, setNewEmployee] = useState({ name: '', email: '', phone: '' });
  const [showAddForm, setShowAddForm] = useState(false);

  const handleAddEmployee = async () => {
    if (!newEmployee.name.trim()) return;

    try {
      const { error } = await supabase
        .from('oregon_tires_employees')
        .insert([{
          name: newEmployee.name,
          email: newEmployee.email || null,
          phone: newEmployee.phone || null
        }]);

      if (error) throw error;

      setNewEmployee({ name: '', email: '', phone: '' });
      setShowAddForm(false);
      
      toast({
        title: "Success",
        description: "Employee added successfully",
      });
    } catch (error) {
      console.error('Error adding employee:', error);
      toast({
        title: "Error",
        description: "Failed to add employee",
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
        title: "Success",
        description: "Employee updated successfully",
      });
    } catch (error) {
      console.error('Error updating employee:', error);
      toast({
        title: "Error",
        description: "Failed to update employee",
        variant: "destructive",
      });
    }
  };

  const startEditing = (employee: Employee) => {
    setEditingId(employee.id);
    setEditingData({
      name: employee.name,
      email: employee.email,
      phone: employee.phone
    });
  };

  const cancelEditing = () => {
    setEditingId(null);
    setEditingData({});
  };

  if (loading) {
    return <div className="text-green-700">Loading employees...</div>;
  }

  return (
    <Card className="border-2 border-green-700">
      <CardHeader className="bg-green-700 text-white">
        <div className="flex items-center justify-between">
          <CardTitle>Employee Management</CardTitle>
          <Button 
            variant="secondary" 
            size="sm"
            onClick={() => setShowAddForm(!showAddForm)}
          >
            <Plus className="h-4 w-4 mr-1" />
            Add Employee
          </Button>
        </div>
      </CardHeader>
      <CardContent className="p-4">
        {showAddForm && (
          <div className="mb-6 p-4 border rounded-lg bg-gray-50">
            <h4 className="font-medium mb-3">Add New Employee</h4>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <Label htmlFor="name">Name *</Label>
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
                  setNewEmployee({ name: '', email: '', phone: '' });
                }}
              >
                <X className="h-4 w-4 mr-1" />
                Cancel
              </Button>
            </div>
          </div>
        )}

        <div className="space-y-3">
          {employees.map((employee) => (
            <div 
              key={employee.id} 
              className="flex items-center justify-between p-3 border rounded-lg bg-white"
            >
              {editingId === employee.id ? (
                <div className="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3 items-center">
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
                  <div className="flex-1">
                    <div className="font-medium">{employee.name}</div>
                    <div className="text-sm text-gray-600">
                      {employee.email && <span>{employee.email}</span>}
                      {employee.email && employee.phone && <span> • </span>}
                      {employee.phone && <span>{employee.phone}</span>}
                    </div>
                  </div>
                  <div className="flex items-center gap-3">
                    <div className="flex items-center gap-2">
                      <Label htmlFor={`active-${employee.id}`} className="text-sm">
                        Active
                      </Label>
                      <Switch
                        id={`active-${employee.id}`}
                        checked={employee.is_active}
                        onCheckedChange={(checked) => 
                          handleUpdateEmployee(employee.id, { is_active: checked })
                        }
                      />
                    </div>
                    <Button 
                      variant="outline" 
                      size="sm"
                      onClick={() => startEditing(employee)}
                    >
                      <Edit2 className="h-4 w-4" />
                    </Button>
                  </div>
                </>
              )}
            </div>
          ))}
        </div>

        {employees.length === 0 && (
          <div className="text-center py-8 text-gray-500">
            No employees found. Add your first employee above.
          </div>
        )}
      </CardContent>
    </Card>
  );
};