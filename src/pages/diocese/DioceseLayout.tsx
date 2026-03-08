import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Building2, Users, Shield, LogOut
} from "lucide-react";
import type { NavSection } from "../../components/elct/AdminSidebar";

const BASE = "/elct/diocese";

const navigation: NavSection[] = [
  {
    title: "Home",
    items: [{ label: "Dashboard", icon: LayoutDashboard, href: BASE }],
  },
  {
    title: "Administration",
    items: [
      {
        label: "Provinces", icon: Building2,
        children: [
          { label: "Create Province Admin", href: `${BASE}/create-province-admin` },
          { label: "All Provinces", href: `${BASE}/provinces` },
          { label: "Province Admins", href: `${BASE}/province-admins` },
        ],
      },
      {
        label: "Diocese Admins", icon: Shield,
        children: [
          { label: "Manage Admins", href: `${BASE}/admins` },
        ],
      },
    ],
  },
  {
    title: "Reports",
    items: [
      {
        label: "Overview", icon: Users,
        children: [
          { label: "Diocese Overview", href: `${BASE}/overview` },
        ],
      },
    ],
  },
  {
    title: "Auth",
    items: [{ label: "Sign Out", icon: LogOut, href: "/" }],
  },
];

export default function DioceseLayout() {
  return <AdminLayout navigation={navigation} levelLabel="Diocese" basePath={BASE} />;
}
