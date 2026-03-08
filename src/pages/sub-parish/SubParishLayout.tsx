import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Users, Shield, LogOut, Coins, Flag, Mail,
  BookOpen, CalendarDays, Receipt, Wallet, FileText, UserCheck, Ban
} from "lucide-react";
import type { NavSection } from "../../components/elct/AdminSidebar";

const BASE = "/elct/sub-parish";

const navigation: NavSection[] = [
  {
    title: "Home",
    items: [{ label: "Dashboard", icon: LayoutDashboard, href: BASE }],
  },
  {
    title: "Administration",
    items: [
      {
        label: "System Users", icon: Shield,
        children: [
          { label: "Create Admin", href: `${BASE}/create-admin` },
        ],
      },
    ],
  },
  {
    title: "Church Management",
    items: [
      {
        label: "Church Members", icon: Users,
        children: [
          { label: "Register Member", href: `${BASE}/register-church-member` },
          { label: "Manage Members", href: `${BASE}/church-members` },
        ],
      },
      {
        label: "Sunday Services", icon: BookOpen,
        children: [
          { label: "View Services", href: `${BASE}/services` },
        ],
      },
    ],
  },
  {
    title: "Events",
    items: [
      { label: "Meetings", icon: CalendarDays, href: `${BASE}/meetings` },
    ],
  },
  {
    title: "Revenues",
    items: [
      {
        label: "Revenue", icon: Coins,
        children: [
          { label: "Add Revenue Stream", href: `${BASE}/add-revenue-stream` },
          { label: "Revenue Streams", href: `${BASE}/revenue-streams` },
          { label: "Record Revenue", href: `${BASE}/record-revenue` },
        ],
      },
      {
        label: "Debits", icon: Receipt,
        children: [
          { label: "Record Debit", href: `${BASE}/record-debit` },
        ],
      },
    ],
  },
  {
    title: "Expenses",
    items: [
      {
        label: "Expense Management", icon: Wallet,
        children: [
          { label: "Create Expense Groups", href: `${BASE}/create-expense-groups` },
          { label: "Record Expense Names", href: `${BASE}/record-expense-names` },
          { label: "Set Budgets", href: `${BASE}/set-expense-budget` },
        ],
      },
      {
        label: "Expense Requests", icon: FileText,
        children: [
          { label: "Make Request", href: `${BASE}/make-expense-request` },
          { label: "All Requests", href: `${BASE}/expense-requests` },
        ],
      },
    ],
  },
  {
    title: "Church Programs",
    items: [
      {
        label: "Harambee", icon: Flag,
        children: [
          { label: "Record Harambee", href: `${BASE}/record-harambee` },
          { label: "Harambee Details", href: `${BASE}/harambee` },
          { label: "Distribute Harambee", href: `${BASE}/distribute-harambee` },
          { label: "Distribution Status", href: `${BASE}/harambee-distribution` },
          { label: "Set Member Target", href: `${BASE}/record-harambee-target` },
          { label: "Record Contribution", href: `${BASE}/record-harambee-contribution` },
          { label: "Contributions", href: `${BASE}/harambee-contribution` },
          { label: "HP Report", href: `${BASE}/head-parish-harambee-report` },
          { label: "Contribution Summary", href: `${BASE}/harambee-contribution-summary` },
          { label: "Community Report", href: `${BASE}/harambee-community-report` },
          { label: "Groups Report", href: `${BASE}/harambee-groups-report` },
        ],
      },
      {
        label: "Harambee Groups", icon: UserCheck,
        children: [
          { label: "Create Group", href: `${BASE}/create-harambee-group` },
          { label: "Assign Member", href: `${BASE}/assign-member-to-group` },
          { label: "All Groups", href: `${BASE}/harambee-groups` },
        ],
      },
      {
        label: "Harambee Exclusion", icon: Ban,
        children: [
          { label: "Exclude Member", href: `${BASE}/exclude-member-from-harambee` },
        ],
      },
      {
        label: "Envelope", icon: Mail,
        children: [
          { label: "Set Member Target", href: `${BASE}/set-envelope-target` },
          { label: "Record Contribution", href: `${BASE}/record-envelope-contribution` },
          { label: "Manage Envelopes", href: `${BASE}/manage-envelopes` },
        ],
      },
    ],
  },
  {
    title: "Auth",
    items: [{ label: "Sign Out", icon: LogOut, href: "/" }],
  },
];

export default function SubParishLayout() {
  return <AdminLayout navigation={navigation} levelLabel="Sub Parish" basePath={BASE} />;
}
