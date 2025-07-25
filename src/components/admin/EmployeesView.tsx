import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { EmployeeManager } from './EmployeeManager';
import { Users } from 'lucide-react';
import { useLanguage } from '@/hooks/useLanguage';

export const EmployeesView = () => {
  const { t } = useLanguage();
  
  return (
    <div className="space-y-6">
      <Card className="border-2 border-green-700">
        <CardHeader className="bg-green-700 text-white">
          <CardTitle className="flex items-center gap-2">
            <Users className="h-5 w-5" />
            {t.admin.employeeManagement}
          </CardTitle>
          <CardDescription className="text-white/80">
            {t.admin.manageTeamMembers}
          </CardDescription>
        </CardHeader>
      </Card>

      <EmployeeManager />
    </div>
  );
};