import AdminLayout from "../../components/elct/AdminLayout";
import {
  LayoutDashboard, Users, BookOpen, CreditCard, Coins, Wallet, Receipt,
  Flag, Mail, FileText, Settings, LogOut
} from "lucide-react";
import type { NavSection } from "../../components/elct/AdminSidebar";

const BASE = "/elct/group";

const navigation: NavSection[] = [
  {
    title: "Home",
    items: [{ label: "Dashboard", icon: LayoutDashboard, href: BASE }],
  },
  {
    title: "Administration",
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
          { label: "Services", href: `${BASE}/services` },
        ],
      },
    ],
  },
  {
    title: "Banking & Finance",
    items: [
      {
        label: "Bank Accounts", icon: CreditCard,
        children: [
          { label: "Add Bank Account", href: `${BASE}/register-bank-account` },
          { label: "Manage Accounts", href: `${BASE}/bank-accounts` },
        ],
      },
    ],
  },
  {
    title: "Revenues",
    items: [
      {
        label: "Revenues", icon: Coins,
        children: [
          { label: "Add Revenue Stream", href: `${BASE}/add-revenue-stream` },
          { label: "Manage Streams", href: `${BASE}/revenue-streams` },
          { label: "Record Revenue", href: `${BASE}/record-revenue` },
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
          { label: "Set Expense Budgets", href: `${BASE}/set-expense-budget` },
        ],
      },
      {
        label: "Expense Requests", icon: Receipt,
        children: [
          { label: "Make Request", href: `${BASE}/make-expense-request` },
          { label: "View Requests", href: `${BASE}/expense-requests` },
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
          { label: "Set Member Target", href: `${BASE}/record-harambee-target` },
          { label: "Record Contribution", href: `${BASE}/record-harambee-contribution` },
          { label: "Contributions", href: `${BASE}/harambee-contribution` },
        ],
      },
      {
        label: "Envelope", icon: Mail,
        children: [
          { label: "Set Target", href: `${BASE}/set-envelope-target` },
          { label: "Record Contribution", href: `${BASE}/record-envelope-contribution` },
          { label: "Manage Envelopes", href: `${BASE}/manage-envelopes` },
        ],
      },
    ],
  },
  {
    title: "Reports",
    items: [
      {
        label: "Harambee Reports", icon: FileText,
        children: [
          { label: "Group Report", href: `${BASE}/group-harambee-report` },
        ],
      },
    ],
  },
  {
    title: "Configuration",
    items: [
      {
        label: "Payment Wallets", icon: Settings,
        children: [
          { label: "Record Wallet", href: `${BASE}/register-payment-gateway-wallet` },
          { label: "Manage Wallets", href: `${BASE}/payment-gateway-wallets` },
        ],
      },
    ],
  },
  {
    title: "Auth",
    items: [{ label: "Sign Out", icon: LogOut, href: "/" }],
  },
];

export default function GroupLayout() {
  return <AdminLayout navigation={navigation} levelLabel="Group" basePath={BASE} />;
}
