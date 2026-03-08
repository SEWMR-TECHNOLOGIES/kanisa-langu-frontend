import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Building2, Users, Shield, LogOut, CreditCard, FileText, Church
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
          { label: "Province List", href: `${BASE}/provinces` },
          { label: "Create Province Admin", href: `${BASE}/create-province-admin` },
          { label: "Province Admins", href: `${BASE}/province-admins` },
        ],
      },
      {
        label: "Head Parishes", icon: Church,
        children: [
          { label: "Register Head Parish", href: `${BASE}/register-head-parish` },
          { label: "Manage Head Parishes", href: `${BASE}/manage-head-parishes` },
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
    title: "Payments",
    items: [
      {
        label: "Payments", icon: CreditCard,
        children: [
          { label: "Manage Payments", href: `${BASE}/manage-payments` },
          { label: "Payment Reports", href: `${BASE}/payment-reports` },
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
      {
        label: "Reports", icon: FileText,
        children: [
          { label: "Sales Report", href: `${BASE}/sales-report` },
          { label: "SMS Usage", href: `${BASE}/sms-usage-report` },
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
