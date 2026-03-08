import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Building2, Users, Shield, LogOut, Church, CreditCard, FileText
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
        label: "Province Admins", icon: Shield,
        children: [
          { label: "Create Admin", href: `${BASE}/create-province-admin` },
          { label: "Admins List", href: `${BASE}/province-admins` },
        ],
      },
      {
        label: "Head Parishes", icon: Church,
        children: [
          { label: "All Head Parishes", href: `${BASE}/head-parishes` },
          { label: "Create HP Admin", href: `${BASE}/create-hp-admin` },
          { label: "HP Admins List", href: `${BASE}/hp-admins` },
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

export default function ProvinceLayout() {
  return <AdminLayout navigation={navigation} levelLabel="Province" basePath={BASE} />;
}
