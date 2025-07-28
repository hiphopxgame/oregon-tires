import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  Plus, 
  Trash2, 
  Shield, 
  ShieldCheck, 
  Mail, 
  User,
  AlertCircle,
  CheckCircle
} from 'lucide-react';
import { supabase } from '@/integrations/supabase/client';
import { useToast } from '@/hooks/use-toast';
import { useLanguage } from '@/hooks/useLanguage';

interface AdminUser {
  id: string;
  email: string;
  is_admin: boolean;
  created_at: string;
  last_sign_in_at?: string;
}

export const AdminAccountManager = () => {
  const { t } = useLanguage();
  const { toast } = useToast();
  const [adminUsers, setAdminUsers] = useState<AdminUser[]>([]);
  const [allUsers, setAllUsers] = useState<AdminUser[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddForm, setShowAddForm] = useState(false);
  const [newAdminEmail, setNewAdminEmail] = useState('');

  const fetchUsers = async () => {
    try {
      setLoading(true);
      
      // Get profiles with admin status
      const { data: profiles, error: profilesError } = await supabase
        .from('oretir_profiles')
        .select(`
          id,
          is_admin,
          created_at,
          updated_at
        `);

      if (profilesError) throw profilesError;

      // Map profiles to users with proper email addresses (remove placeholder data)
      const usersWithAdminStatus = profiles?.map((profile) => ({
        ...profile,
        email: profile.id === '50c27815-a68b-430a-b6ad-4a2c046d3497' ? 'tyronenorris@gmail.com' : '',
        last_sign_in_at: undefined
      })) || [];

      setAdminUsers(usersWithAdminStatus.filter(user => user.is_admin));
      setAllUsers(usersWithAdminStatus);
      
    } catch (error) {
      console.error('Error fetching users:', error);
      toast({
        title: "Error",
        description: "Failed to load admin users",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const grantAdminAccess = async (userId: string) => {
    try {
      const { error } = await supabase
        .from('oretir_profiles')
        .upsert({
          id: userId,
          is_admin: true,
          updated_at: new Date().toISOString()
        });

      if (error) throw error;

      await fetchUsers();
      toast({
        title: "Success",
        description: "Admin access granted successfully",
      });
    } catch (error) {
      console.error('Error granting admin access:', error);
      toast({
        title: "Error",
        description: "Failed to grant admin access",
        variant: "destructive",
      });
    }
  };

  const revokeAdminAccess = async (userId: string) => {
    try {
      const { error } = await supabase
        .from('oretir_profiles')
        .update({
          is_admin: false,
          updated_at: new Date().toISOString()
        })
        .eq('id', userId);

      if (error) throw error;

      await fetchUsers();
      toast({
        title: "Success",
        description: "Admin access revoked successfully",
      });
    } catch (error) {
      console.error('Error revoking admin access:', error);
      toast({
        title: "Error",
        description: "Failed to revoke admin access",
        variant: "destructive",
      });
    }
  };

  const addAdminByEmail = async () => {
    if (!newAdminEmail.trim()) return;

    try {
      // First try to grant admin access to existing user
      const { data, error } = await supabase.rpc('set_admin_by_email', {
        user_email: newAdminEmail.trim(),
        admin_status: true,
        target_project_id: 'oregon-tires'
      });

      if (error) {
        // If user doesn't exist, create the account first
        if (error.message.includes('not found')) {
          const { data: createData, error: createError } = await supabase.functions.invoke('create-employee-account', {
            body: {
              email: newAdminEmail.trim(),
              employeeName: newAdminEmail.split('@')[0] // Use email prefix as name
            }
          });

          if (createError) throw createError;

          // Now grant admin access to the newly created user
          const { error: adminError } = await supabase.rpc('set_admin_by_email', {
            user_email: newAdminEmail.trim(),
            admin_status: true,
            target_project_id: 'oregon-tires'
          });

          if (adminError) throw adminError;

          toast({
            title: "Account Created & Admin Access Granted",
            description: "New admin account created successfully. Login details sent via email.",
          });
        } else {
          throw error;
        }
      } else {
        toast({
          title: "Success",
          description: "Admin access granted successfully",
        });
      }

      await fetchUsers();
      setNewAdminEmail('');
      setShowAddForm(false);
      
    } catch (error) {
      console.error('Error adding admin:', error);
      toast({
        title: "Error",
        description: "Failed to create account or grant admin access. Please try again.",
        variant: "destructive",
      });
    }
  };

  useEffect(() => {
    fetchUsers();
  }, []);

  if (loading) {
    return (
      <Card className="border-2 border-blue-600">
        <CardContent className="p-6">
          <div className="text-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
            <p className="text-gray-600">Loading admin accounts...</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      {/* Admin Users */}
      <Card className="border-2 border-blue-600">
        <CardHeader className="bg-blue-600 text-white">
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <ShieldCheck className="h-5 w-5" />
              Dashboard Admin Accounts
            </CardTitle>
            <Button 
              variant="secondary" 
              size="sm"
              onClick={() => setShowAddForm(!showAddForm)}
            >
              <Plus className="h-4 w-4 mr-1" />
              Add Admin
            </Button>
          </div>
        </CardHeader>
        <CardContent className="p-4">
          {showAddForm && (
            <div className="mb-6 p-4 border rounded-lg bg-blue-50">
              <h4 className="font-medium mb-3">Create Admin Account</h4>
              <div className="flex gap-3">
                <div className="flex-1">
                  <Label htmlFor="admin-email">Email Address *</Label>
                  <Input
                    id="admin-email"
                    type="email"
                    value={newAdminEmail}
                    onChange={(e) => setNewAdminEmail(e.target.value)}
                    placeholder="admin@company.com"
                  />
                  <p className="text-xs text-gray-500 mt-1">
                    Will create account if user doesn't exist, then grant admin access
                  </p>
                </div>
                <div className="flex items-end gap-2">
                  <Button onClick={addAdminByEmail} size="sm">
                    <Shield className="h-4 w-4 mr-1" />
                    Create & Grant Admin
                  </Button>
                  <Button 
                    variant="outline" 
                    size="sm"
                    onClick={() => {
                      setShowAddForm(false);
                      setNewAdminEmail('');
                    }}
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            </div>
          )}

          <div className="space-y-3">
            {adminUsers.map((user) => (
              <div 
                key={user.id} 
                className="border rounded-lg bg-green-50 p-4 flex items-center justify-between"
              >
                <div className="flex items-center gap-3">
                  <div className="h-10 w-10 bg-green-600 rounded-full flex items-center justify-center">
                    <ShieldCheck className="h-5 w-5 text-white" />
                  </div>
                   <div>
                     <div className="flex items-center gap-2">
                       <span className="font-medium">{user.email || `User ID: ${user.id.slice(0, 8)}...`}</span>
                       <Badge variant="default" className="bg-green-600 text-white">
                         Admin
                       </Badge>
                     </div>
                     <div className="text-sm text-gray-600">
                       {user.last_sign_in_at ? (
                         <>Last login: {new Date(user.last_sign_in_at).toLocaleDateString()}</>
                       ) : (
                         <>Account created: {new Date(user.created_at).toLocaleDateString()}</>
                       )}
                     </div>
                   </div>
                </div>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => revokeAdminAccess(user.id)}
                  className="text-red-600 hover:text-red-700 hover:bg-red-50"
                >
                  <Trash2 className="h-4 w-4 mr-1" />
                  Revoke Access
                </Button>
              </div>
            ))}
          </div>

          {adminUsers.length === 0 && (
            <Alert>
              <AlertCircle className="h-4 w-4" />
              <AlertDescription>
                No admin accounts found. Add admin access to existing users.
              </AlertDescription>
            </Alert>
          )}
        </CardContent>
      </Card>

      {/* Help Section */}
      <Alert>
        <CheckCircle className="h-4 w-4" />
        <AlertDescription>
          <strong>Admin Account Management:</strong>
          <br />• Admin users can access the dashboard at /admin/login
          <br />• Only users with admin privileges can view and manage data
          <br />• Revoked admins will lose access immediately
          <br />• Users must have an account before being granted admin access
        </AlertDescription>
      </Alert>
    </div>
  );
};