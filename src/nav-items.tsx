
import React from 'react';
import { Home } from 'lucide-react';
import Index from '@/pages/Index';
import OregonTires from '@/pages/OregonTires';
import OregonTiresAdmin from '@/pages/OregonTiresAdmin';

export interface NavItem {
  title: string;
  to: string;
  icon: React.ComponentType<{ className?: string }>;
  page: React.ReactElement;
}

export const navItems: NavItem[] = [
  {
    title: "Home",
    to: "/",
    icon: Home,
    page: <Index />,
  },
  {
    title: "Oregon Tires",
    to: "/oregon-tires",
    icon: Home,
    page: <OregonTires />,
  },
  {
    title: "Admin",
    to: "/admin",
    icon: Home,
    page: <OregonTiresAdmin />,
  },
];
