import { useState } from "react";
import { Link, useLocation } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import {
  LayoutDashboard, Building2, Users, Shield, UserCheck, Music, BookOpen,
  CalendarDays, CalendarPlus, Bell, CreditCard, Coins, Receipt, Wallet,
  Flag, Mail, BarChart3, Settings, LogOut, ChevronDown,
  X, Ban, Building, TrendingUp, Package, FileText
} from "lucide-react";

const BASE = "/elct/head-parish";

interface NavItem {
  label: string;
  icon: React.ElementType;
  href?: string;
  children?: { label: string; href: string }[];
}

interface NavSection {
  title: string;
  items: NavItem[];
}

const navigation: NavSection[] = [
  {
    title: "Home",
    items: [
      { label: "Dashboard", icon: LayoutDashboard, href: `${BASE}` },
    ],
  },
  {
    title: "Administration",
    items: [
      {
        label: "Sub Parishes", icon: Building2,
        children: [
          { label: "Add Sub Parish", href: `${BASE}/register-sub-parish` },
          { label: "Manage Sub Parishes", href: `${BASE}/sub-parishes` },
        ],
      },
      {
        label: "Communities", icon: Users,
        children: [
          { label: "Add Community", href: `${BASE}/register-community` },
          { label: "Manage Communities", href: `${BASE}/communities` },
        ],
      },
      {
        label: "Groups", icon: Users,
        children: [
          { label: "Add Group", href: `${BASE}/register-group` },
          { label: "Manage Groups", href: `${BASE}/groups` },
        ],
      },
    ],
  },
  {
    title: "Roles",
    items: [
      {
        label: "System Users", icon: Shield,
        children: [
          { label: "Create User", href: `${BASE}/create-admin` },
        ],
      },
    ],
  },
  {
    title: "Church Management",
    items: [
      {
        label: "Church Leaders", icon: UserCheck,
        children: [
          { label: "Register Leader", href: `${BASE}/register-church-leader` },
          { label: "Manage Leaders", href: `${BASE}/church-leaders` },
        ],
      },
      {
        label: "Church Members", icon: Users,
        children: [
          { label: "Register Member", href: `${BASE}/register-church-member` },
          { label: "Upload From File", href: `${BASE}/upload-church-members` },
          { label: "Manage Members", href: `${BASE}/church-members` },
          { label: "Active Accounts", href: `${BASE}/church-members-accounts` },
          { label: "Download Members List", href: `${BASE}/download-church-members-list` },
        ],
      },
      {
        label: "Member Exclusion", icon: Ban,
        children: [
          { label: "Add Reason", href: `${BASE}/add-exclusion-reason` },
          { label: "View Exclusions", href: `${BASE}/member-exclusions` },
          { label: "Exclude Member", href: `${BASE}/exclude-church-member` },
          { label: "Excluded Members", href: `${BASE}/excluded-church-members` },
        ],
      },
      {
        label: "Church Choirs", icon: Music,
        children: [
          { label: "Register Choir", href: `${BASE}/register-church-choir` },
          { label: "Choirs", href: `${BASE}/church-choirs` },
        ],
      },
      {
        label: "Sunday Services", icon: BookOpen,
        children: [
          { label: "Set Services Count", href: `${BASE}/set-services-count` },
          { label: "Set Service Time", href: `${BASE}/set-service-time` },
          { label: "Services Numbers", href: `${BASE}/services` },
          { label: "Record Services", href: `${BASE}/record-sunday-service` },
          { label: "View Services", href: `${BASE}/sunday-services` },
        ],
      },
    ],
  },
  {
    title: "Events",
    items: [
      {
        label: "Meetings", icon: CalendarDays,
        children: [
          { label: "New Meeting", href: `${BASE}/new-meeting` },
          { label: "All Meetings", href: `${BASE}/all-meetings` },
        ],
      },
      {
        label: "Church Events", icon: CalendarPlus,
        children: [
          { label: "Create Event", href: `${BASE}/new-church-event` },
          { label: "All Events", href: `${BASE}/church-events` },
        ],
      },
      {
        label: "Attendance", icon: UserCheck,
        children: [
          { label: "Set Benchmark", href: `${BASE}/set-attendance-benchmark` },
          { label: "Record Attendance", href: `${BASE}/record-attendance` },
        ],
      },
      { label: "Push Notification", icon: Bell, href: `${BASE}/send-push-notification` },
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
          { label: "Record Transactions", href: `${BASE}/record-parish-transactions` },
          { label: "Financial Statement", href: `${BASE}/financial-statement` },
        ],
      },
    ],
  },
  {
    title: "Revenues & Debits",
    items: [
      {
        label: "Revenues", icon: Coins,
        children: [
          { label: "Create Revenue Groups", href: `${BASE}/create-revenue-groups` },
          { label: "Add Revenue Stream", href: `${BASE}/add-revenue-stream` },
          { label: "Manage Revenue Streams", href: `${BASE}/revenue-streams` },
          { label: "Map Revenue Streams", href: `${BASE}/map-revenue-streams` },
          { label: "Link Revenue Stream", href: `${BASE}/link-revenue-stream` },
          { label: "Record Revenue", href: `${BASE}/record-revenue` },
          { label: "Verify Revenues", href: `${BASE}/verify-revenues` },
          { label: "Envelope Usage", href: `${BASE}/envelope-usage` },
          { label: "Set Collection Targets", href: `${BASE}/set-annual-revenue-target` },
          { label: "Set Stream Targets", href: `${BASE}/set-revenue-stream-target` },
          { label: "Distribute Targets", href: `${BASE}/distribute-annual-revenue-target` },
        ],
      },
      {
        label: "Debits & Loans", icon: Receipt,
        children: [
          { label: "Record Debit", href: `${BASE}/record-debit` },
          { label: "All Debits", href: `${BASE}/debits` },
        ],
      },
    ],
  },
  {
    title: "Budgeting & Expenses",
    items: [
      {
        label: "Budgeting", icon: TrendingUp,
        children: [
          { label: "OGO", href: `${BASE}/ogo` },
        ],
      },
      {
        label: "Expense Management", icon: Wallet,
        children: [
          { label: "Create Expense Groups", href: `${BASE}/create-expense-groups` },
          { label: "Record Expense Names", href: `${BASE}/record-expense-names` },
          { label: "Set Annual Budgets", href: `${BASE}/set-annual-expense-budget` },
          { label: "Distribute Budgets", href: `${BASE}/distribute-annual-expense-budget` },
          { label: "Allocate Budgets", href: `${BASE}/set-expense-budget` },
        ],
      },
      {
        label: "Expense Requests", icon: FileText,
        children: [
          { label: "Make Request", href: `${BASE}/make-expense-request` },
          { label: "Grouped Requests", href: `${BASE}/grouped-requests` },
          { label: "All Requests", href: `${BASE}/expense-requests` },
        ],
      },
    ],
  },
  {
    title: "Assets",
    items: [
      {
        label: "Assets Management", icon: Package,
        children: [
          { label: "Add New Asset", href: `${BASE}/add-asset` },
          { label: "Set Asset Status", href: `${BASE}/set-asset-status` },
          { label: "Record Asset Revenue", href: `${BASE}/record-asset-revenue` },
          { label: "Record Asset Expenses", href: `${BASE}/record-asset-expenses` },
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
          { label: "Record Harambee Class", href: `${BASE}/record-harambee-classes` },
          { label: "Classes", href: `${BASE}/harambee-classes` },
          { label: "Record New Harambee", href: `${BASE}/record-harambee` },
          { label: "Harambee Details", href: `${BASE}/harambee` },
          { label: "Distribute Harambee", href: `${BASE}/distribute-harambee` },
          { label: "Distribution Status", href: `${BASE}/harambee-distribution` },
          { label: "Set Member Target", href: `${BASE}/record-harambee-target` },
          { label: "Upload Targets", href: `${BASE}/upload-harambee-targets` },
          { label: "Record Contribution", href: `${BASE}/record-harambee-contribution` },
          { label: "Contributions", href: `${BASE}/harambee-contribution` },
          { label: "Send Contribution SMS", href: `${BASE}/send-harambee-contribution-sms` },
          { label: "Send Summary SMS", href: `${BASE}/send-harambee-summary-message` },
          { label: "Send Notification", href: `${BASE}/send-harambee-contribution-notification` },
          { label: "Harambee Letter", href: `${BASE}/generate-harambee-letter` },
          { label: "Letter Status", href: `${BASE}/harambee-letter-status` },
          { label: "Non Participants", href: `${BASE}/non-harambee-members` },
          { label: "Record Expenses", href: `${BASE}/record-harambee-expenses` },
        ],
      },
      {
        label: "Harambee Groups", icon: Users,
        children: [
          { label: "Create Group", href: `${BASE}/create-harambee-group` },
          { label: "Assign Member", href: `${BASE}/assign-member-to-group` },
          { label: "All Groups", href: `${BASE}/harambee-groups` },
        ],
      },
      {
        label: "Harambee Exclusion", icon: Ban,
        children: [
          { label: "Add Reason", href: `${BASE}/add-harambee-exclusion-reason` },
          { label: "View Exclusions", href: `${BASE}/harambee-exclusions` },
          { label: "Exclude Member", href: `${BASE}/exclude-church-member-from-harambee` },
          { label: "Excluded Members", href: `${BASE}/excluded-church-members-from-harambee` },
        ],
      },
      {
        label: "Envelope", icon: Mail,
        children: [
          { label: "Set Parish Target", href: `${BASE}/set-annual-envelope-target` },
          { label: "Distribute Target", href: `${BASE}/distribute-annual-envelope-target` },
          { label: "Set Member Target", href: `${BASE}/set-envelope-target` },
          { label: "Record Contribution", href: `${BASE}/record-envelope-contribution` },
          { label: "Upload From File", href: `${BASE}/upload-envelope-data` },
          { label: "Manage Envelopes", href: `${BASE}/manage-envelopes` },
          { label: "Envelope Usage", href: `${BASE}/envelope-usage-summary` },
        ],
      },
    ],
  },
  {
    title: "Reports & Insights",
    items: [
      {
        label: "Finances", icon: Wallet,
        children: [
          { label: "Revenue Breakdown", href: `${BASE}/download-revenue-breakdown` },
          { label: "Revenue Statement", href: `${BASE}/download-revenue-statement` },
        ],
      },
      {
        label: "Harambee Reports", icon: BarChart3,
        children: [
          { label: "Head Parish Report", href: `${BASE}/head-parish-harambee-report` },
          { label: "Contribution Summary", href: `${BASE}/harambee-contribution-summary` },
          { label: "Contribution Report", href: `${BASE}/harambee-contribution-report` },
          { label: "Groups Report", href: `${BASE}/harambee-groups-report` },
          { label: "Community Report", href: `${BASE}/harambee-community-report` },
          { label: "By Class", href: `${BASE}/contribution-report-by-class` },
          { label: "Letters Reports", href: `${BASE}/harambee-letters-report` },
          { label: "Clerks Report", href: `${BASE}/clerks-harambee-report` },
        ],
      },
      {
        label: "Revenues & Budgeting", icon: TrendingUp,
        children: [
          { label: "OGO", href: `${BASE}/ogo-report` },
          { label: "Revenue Groups Report", href: `${BASE}/revenue-group-report` },
          { label: "Expense Groups Report", href: `${BASE}/expense-group-report` },
        ],
      },
    ],
  },
  {
    title: "Third Parties",
    items: [
      {
        label: "Payment Wallets", icon: Wallet,
        children: [
          { label: "Record Wallet", href: `${BASE}/register-payment-gateway-wallet` },
          { label: "Manage Wallets", href: `${BASE}/payment-gateway-wallets` },
        ],
      },
      {
        label: "SMS API Gateway", icon: Settings,
        children: [
          { label: "Record API Info", href: `${BASE}/record-sms-api-info` },
        ],
      },
    ],
  },
  {
    title: "Auth",
    items: [
      { label: "Sign Out", icon: LogOut, href: "/" },
    ],
  },
];

function NavItemComponent({ item, isCollapsed }: { item: NavItem; isCollapsed: boolean }) {
  const location = useLocation();
  const [open, setOpen] = useState(() => {
    if (!item.children) return false;
    return item.children.some(c => location.pathname === c.href);
  });

  const isActive = item.href ? location.pathname === item.href : false;
  const hasActiveChild = item.children?.some(c => location.pathname === c.href) || false;

  if (item.href) {
    return (
      <Link
        to={item.href}
        className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-200 group
          ${isActive
            ? "bg-admin-surface-active text-admin-accent"
            : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
          }`}
      >
        <item.icon className={`w-[18px] h-[18px] flex-shrink-0 transition-colors ${isActive ? "text-admin-accent" : "text-admin-text group-hover:text-admin-text-bright"}`} />
        {!isCollapsed && <span>{item.label}</span>}
      </Link>
    );
  }

  return (
    <div>
      <button
        onClick={() => setOpen(!open)}
        className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-200 group
          ${hasActiveChild
            ? "text-admin-text-bright bg-admin-surface-hover"
            : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
          }`}
      >
        <item.icon className={`w-[18px] h-[18px] flex-shrink-0 ${hasActiveChild ? "text-admin-accent" : "text-admin-text group-hover:text-admin-text-bright"}`} />
        {!isCollapsed && (
          <>
            <span className="flex-1 text-left">{item.label}</span>
            <motion.div animate={{ rotate: open ? 180 : 0 }} transition={{ duration: 0.2 }}>
              <ChevronDown className="w-3.5 h-3.5 opacity-50" />
            </motion.div>
          </>
        )}
      </button>
      <AnimatePresence>
        {open && !isCollapsed && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="overflow-hidden"
          >
            <div className="ml-4 pl-4 border-l border-admin-border/50 mt-1 space-y-0.5">
              {item.children?.map((child) => {
                const childActive = location.pathname === child.href;
                return (
                  <Link
                    key={child.href}
                    to={child.href}
                    className={`block px-3 py-2 rounded-md text-[12.5px] transition-all duration-150
                      ${childActive
                        ? "text-admin-accent bg-admin-surface-active font-medium"
                        : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                      }`}
                  >
                    {child.label}
                  </Link>
                );
              })}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

interface HeadParishSidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function HeadParishSidebar({ isOpen, onClose }: HeadParishSidebarProps) {
  return (
    <>
      {/* Mobile overlay */}
      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
            onClick={onClose}
          />
        )}
      </AnimatePresence>

      {/* Sidebar */}
      <aside
        className={`fixed top-0 left-0 h-full z-50 bg-admin-bg border-r border-admin-border w-[280px] flex flex-col transition-transform duration-300 ease-out lg:translate-x-0 ${
          isOpen ? "translate-x-0" : "-translate-x-full"
        }`}
      >
        {/* Brand */}
        <div className="flex items-center justify-between px-5 py-5 border-b border-admin-border/50">
          <Link to="/head-parish" className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-admin-accent to-amber-600 flex items-center justify-center">
              <Building className="w-5 h-5 text-admin-bg" />
            </div>
            <div>
              <h2 className="text-sm font-bold text-admin-text-bright tracking-tight">Kanisa Langu</h2>
              <p className="text-[10px] text-admin-text uppercase tracking-widest">Head Parish</p>
            </div>
          </Link>
          <button onClick={onClose} className="lg:hidden p-1.5 rounded-lg hover:bg-admin-surface-hover text-admin-text">
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Navigation */}
        <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-6 scrollbar-thin">
          {navigation.map((section) => (
            <div key={section.title}>
              <p className="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-admin-text/50">
                {section.title}
              </p>
              <div className="space-y-0.5">
                {section.items.map((item) => (
                  <NavItemComponent key={item.label} item={item} isCollapsed={false} />
                ))}
              </div>
            </div>
          ))}
        </nav>

        {/* Footer */}
        <div className="p-4 border-t border-admin-border/50">
          <div className="flex items-center gap-3 px-2">
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-admin-accent/20 to-admin-accent/5 flex items-center justify-center">
              <span className="text-xs font-bold text-admin-accent">A</span>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-xs font-medium text-admin-text-bright truncate">Admin User</p>
              <p className="text-[10px] text-admin-text truncate">admin@kanisalangu.com</p>
            </div>
          </div>
        </div>
      </aside>
    </>
  );
}
