import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { EmployeeManager } from './EmployeeManager';
import { AdminAccountManager } from './AdminAccountManager';
import { Users, Shield } from 'lucide-react';
import { useLanguage } from '@/hooks/useLanguage';

export const EmployeesView = () => {
  const { t } = useLanguage();
  
  return (
    <div className="space-y-6">
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle className="flex items-center gap-2">
            <Users className="h-5 w-5" />
            Employee & Admin Management
          </CardTitle>
          <CardDescription className="text-white/80">
            Manage team members and dashboard admin accounts
          </CardDescription>
        </CardHeader>
      </Card>

      <Tabs defaultValue="employees" className="w-full">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="employees" className="flex items-center gap-2">
            <Users className="h-4 w-4" />
            Service Team
          </TabsTrigger>
          <TabsTrigger value="admins" className="flex items-center gap-2">
            <Shield className="h-4 w-4" />
            Dashboard Admins
          </TabsTrigger>
        </TabsList>
        
        <TabsContent value="employees" className="mt-6">
          <EmployeeManager />
        </TabsContent>
        
        <TabsContent value="admins" className="mt-6">
          <AdminAccountManager />
        </TabsContent>
      </Tabs>
    </div>
  );
};