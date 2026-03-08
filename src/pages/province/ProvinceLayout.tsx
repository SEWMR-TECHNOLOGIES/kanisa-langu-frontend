import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Building2, Users, Shield, LogOut, Church
} from "lucide-react";
import type { NavSection } from "../../components/elct/AdminSidebar";

const BASE = "/elct/province";

const navigation: NavSection[] = [
  {
    title: "Home",
    items: [{ label: "Dashboard", icon: LayoutDashboard, href: BASE }],
  },
  {
    title: "Administration",
    items: [
      {
        label: "Head Parishes", icon: Church,
        children: [
          { label: "All Head Parishes", href: `${BASE}/head-parishes` },
          { label: "Create HP Admin", href: `${BASE}/create-hp-admin` },
          { label: "HP Admins List", href: `${BASE}/hp-admins` },
        ],
      },
      {
        label: "Province Admins", icon: Shield,
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
        label: "Overview", icon: Building2,
        children: [
          { label: "Province Overview", href: `${BASE}/overview` },
          { label: "Financial Summary", href: `${BASE}/financial-summary` },
        ],
      },
      {
        label: "Members", icon: Users,
        children: [
          { label: "Members Overview", href: `${BASE}/members-overview` },
        ],
      },
    ],
  },
  {
    title: "Auth",
    items: [{ label: "Sign Out", icon: LogOut, href: "/" }],
  },
];

export default function ProvinceLayout() {
  return <AdminLayout navigation={navigation} levelLabel="Province" basePath={BASE} />;
}
