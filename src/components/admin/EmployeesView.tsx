import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { EmployeeManager } from './EmployeeManager';
import { Users } from 'lucide-react';

export const EmployeesView = () => {
  return (
    <div className="space-y-6">
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle className="flex items-center gap-2">
            <Users className="h-5 w-5" />
            Employee Management
          </CardTitle>
          <CardDescription className="text-white/80">
            Manage your team members and their information
          </CardDescription>
        </CardHeader>
      </Card>

      <EmployeeManager />
    </div>
  );
};