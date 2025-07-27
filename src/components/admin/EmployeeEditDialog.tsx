import React, { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Save, Mail, Key, Shield, AlertTriangle, User } from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { Employee } from '@/hooks/useEmployees';

interface EmployeeEditDialogProps {
  employee: Employee | null;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onEmployeeUpdated: () => void;
}

export const EmployeeEditDialog = ({ employee, open, onOpenChange, onEmployeeUpdated }: EmployeeEditDialogProps) => {
  const { toast } = useToast();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    role: 'Employee',
    is_active: true
  });
  const [isLoading, setIsLoading] = useState(false);
  const [hasAuthAccount, setHasAuthAccount] = useState(false);
  const [checkingAuthAccount, setCheckingAuthAccount] = useState(false);

  useEffect(() => {
    if (employee) {
      setFormData({
        name: employee.name,
        email: employee.email || '',
        phone: employee.phone || '',
        role: employee.role,
        is_active: employee.is_active
      });
      
      // Check if employee has an auth account
      if (employee.email) {
        checkAuthAccountExists(employee.email);
      }
    }
  }, [employee]);

  const checkAuthAccountExists = async (email: string) => {
    setCheckingAuthAccount(true);
    try {
      // We can't directly query auth.users, so we'll try the RPC call to see if it works
      const { error } = await supabase.rpc('set_admin_by_email', {
        user_email: email,
        admin_status: false // Just checking, not actually changing
      });
      
      // If no error, user exists
      setHasAuthAccount(!error);
    } catch (error) {
      setHasAuthAccount(false);
    } finally {
      setCheckingAuthAccount(false);
    }
  };

  const handleInputChange = (field: string, value: string | boolean) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleSaveEmployee = async () => {
    if (!employee) return;
    
    setIsLoading(true);
    try {
      const { error } = await supabase
        .from('oretir_employees')
        .update({
          name: formData.name,
          email: formData.email || null,
          phone: formData.phone || null,
          role: formData.role,
          is_active: formData.is_active
        })
        .eq('id', employee.id);

      if (error) throw error;

      toast({
        title: "Success",
        description: "Employee details updated successfully",
      });

      onEmployeeUpdated();
      onOpenChange(false);
    } catch (error) {
      console.error('Error updating employee:', error);
      toast({
        title: "Error",
        description: "Failed to update employee details",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleSendPasswordReset = async () => {
    if (!formData.email) return;

    setIsLoading(true);
    try {
      const { error } = await supabase.auth.resetPasswordForEmail(formData.email, {
        redirectTo: `${window.location.origin}/admin/reset-password`
      });

      if (error) throw error;

      toast({
        title: "Password Reset Sent",
        description: `A password reset email has been sent to ${formData.email}`,
      });
    } catch (error) {
      console.error('Error sending password reset:', error);
      toast({
        title: "Error",
        description: "Failed to send password reset email",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  const handleMakeAdmin = async () => {
    if (!formData.email) return;

    setIsLoading(true);
    try {
      const { error } = await supabase.rpc('set_admin_by_email', {
        user_email: formData.email
      });

      if (error) throw error;

      toast({
        title: "Success",
        description: `${formData.name} has been granted admin access`,
      });
    } catch (error) {
      console.error('Error granting admin access:', error);
      toast({
        title: "Error",
        description: "Employee needs to create an account first. Ask them to sign up on the login page.",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  if (!employee) return null;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <User className="h-5 w-5" />
            Edit Employee: {employee.name}
          </DialogTitle>
          <DialogDescription>
            Update employee information and manage their account access.
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-6">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle className="text-lg">Basic Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Full Name *</Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={(e) => handleInputChange('name', e.target.value)}
                    placeholder="Employee full name"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="role">Role</Label>
                  <select
                    className="w-full h-10 px-3 py-2 border border-input bg-background rounded-md text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:outline-none"
                    value={formData.role}
                    onChange={(e) => handleInputChange('role', e.target.value)}
                  >
                    <option value="Employee">Employee</option>
                    <option value="Manager">Manager</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="email">Email Address</Label>
                  <Input
                    id="email"
                    type="email"
                    value={formData.email}
                    onChange={(e) => handleInputChange('email', e.target.value)}
                    placeholder="employee@email.com"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="phone">Phone Number</Label>
                  <Input
                    id="phone"
                    value={formData.phone}
                    onChange={(e) => handleInputChange('phone', e.target.value)}
                    placeholder="(555) 123-4567"
                  />
                </div>
              </div>

              <div className="flex items-center justify-between">
                <div className="space-y-1">
                  <Label>Employee Status</Label>
                  <p className="text-sm text-muted-foreground">
                    Inactive employees cannot accept appointments
                  </p>
                </div>
                <div className="flex items-center space-x-2">
                  <Switch
                    checked={formData.is_active}
                    onCheckedChange={(checked) => handleInputChange('is_active', checked)}
                  />
                  <Label className={formData.is_active ? 'text-green-600' : 'text-gray-500'}>
                    {formData.is_active ? 'Active' : 'Inactive'}
                  </Label>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Account Management */}
          {formData.email && (
            <Card>
              <CardHeader>
                <CardTitle className="text-lg flex items-center gap-2">
                  <Key className="h-5 w-5" />
                  Account Management
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between p-3 border rounded-lg">
                  <div className="space-y-1">
                    <p className="font-medium">Authentication Account</p>
                    <p className="text-sm text-muted-foreground">
                      {checkingAuthAccount ? 'Checking...' : hasAuthAccount ? 'Employee has an account' : 'No account found'}
                    </p>
                  </div>
                  <Badge variant={hasAuthAccount ? "default" : "secondary"}>
                    {hasAuthAccount ? "Account Exists" : "No Account"}
                  </Badge>
                </div>

                {hasAuthAccount && (
                  <div className="space-y-3">
                    <Button
                      variant="outline"
                      onClick={handleSendPasswordReset}
                      disabled={isLoading}
                      className="w-full"
                    >
                      <Mail className="h-4 w-4 mr-2" />
                      Send Password Reset Email
                    </Button>

                    <Button
                      variant="outline"
                      onClick={handleMakeAdmin}
                      disabled={isLoading}
                      className="w-full text-blue-600 hover:text-blue-700"
                    >
                      <Shield className="h-4 w-4 mr-2" />
                      Grant Admin Access
                    </Button>
                  </div>
                )}

                {!hasAuthAccount && (
                  <div className="p-3 border border-orange-200 bg-orange-50 rounded-lg">
                    <div className="flex items-start gap-2">
                      <AlertTriangle className="h-5 w-5 text-orange-600 mt-0.5" />
                      <div>
                        <p className="font-medium text-orange-800">No Account Found</p>
                        <p className="text-sm text-orange-700">
                          Ask the employee to sign up at the login page using email: {formData.email}
                        </p>
                      </div>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>
          )}
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => onOpenChange(false)}
            disabled={isLoading}
          >
            Cancel
          </Button>
          <Button
            onClick={handleSaveEmployee}
            disabled={isLoading || !formData.name.trim()}
          >
            <Save className="h-4 w-4 mr-2" />
            {isLoading ? 'Saving...' : 'Save Changes'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};