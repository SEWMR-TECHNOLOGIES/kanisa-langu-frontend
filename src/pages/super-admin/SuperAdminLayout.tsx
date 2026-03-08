import { useState } from "react";
import { Outlet, useNavigate, useLocation, Link } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import {
  LayoutDashboard, Globe, MapPin, Building2, Database, Tag, Briefcase, Music,
  CreditCard, BarChart3, LogOut, ChevronDown, Menu, X, User, Bell
} from "lucide-react";
import logo from "../../assets/kanisa-logo.png";

const BASE = "/app";

interface NavGroup {
  label: string;
  icon: React.ElementType;
  items: { label: string; href: string }[];
}

interface NavItem {
  label: string;
  icon: React.ElementType;
  href: string;
}

const navSections: { title: string; items: (NavGroup | NavItem)[] }[] = [
  {
    title: "Home",
    items: [
      { label: "Dashboard", icon: LayoutDashboard, href: BASE },
    ],
  },
  {
    title: "Administration",
    items: [
      {
        label: "Diocese Administration", icon: Globe, items: [
          { label: "Register Diocese", href: `${BASE}/register-diocese` },
          { label: "Manage Dioceses", href: `${BASE}/manage-dioceses` },
          { label: "Create Admins", href: `${BASE}/create-diocese-admin` },
          { label: "Admins List", href: `${BASE}/diocese-admins-list` },
        ],
      },
      {
        label: "Provinces", icon: MapPin, items: [
          { label: "Register Province", href: `${BASE}/register-province` },
          { label: "Manage Provinces", href: `${BASE}/manage-provinces` },
        ],
      },
      {
        label: "Head Parishes", icon: Building2, items: [
          { label: "Register Head Parish", href: `${BASE}/register-head-parish` },
          { label: "Manage Head Parishes", href: `${BASE}/manage-head-parishes` },
        ],
      },
    ],
  },
  {
    title: "Data",
    items: [
      {
        label: "Banks", icon: Database, items: [
          { label: "Register Bank", href: `${BASE}/register-bank` },
          { label: "Manage Banks", href: `${BASE}/manage-banks` },
        ],
      },
      {
        label: "Locations", icon: MapPin, items: [
          { label: "Register Region", href: `${BASE}/register-region` },
          { label: "Manage Regions", href: `${BASE}/manage-regions` },
          { label: "Register District", href: `${BASE}/register-district` },
          { label: "Manage Districts", href: `${BASE}/manage-districts` },
        ],
      },
      {
        label: "Titles", icon: Tag, items: [
          { label: "Add Title", href: `${BASE}/add-title` },
          { label: "Manage Titles", href: `${BASE}/manage-titles` },
        ],
      },
      {
        label: "Occupations", icon: Briefcase, items: [
          { label: "Add Occupation", href: `${BASE}/add-occupation` },
          { label: "Manage Occupations", href: `${BASE}/manage-occupations` },
        ],
      },
      {
        label: "Praise Songs", icon: Music, items: [
          { label: "Register Praise Song", href: `${BASE}/register-praise-song` },
          { label: "Manage Praise Songs", href: `${BASE}/manage-praise-songs` },
        ],
      },
    ],
  },
  {
    title: "Payments",
    items: [
      {
        label: "Payments", icon: CreditCard, items: [
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
        label: "Reports", icon: BarChart3, items: [
          { label: "Sales", href: `${BASE}/sales-report` },
          { label: "SMS Usage", href: `${BASE}/sms-usage-report` },
        ],
      },
    ],
  },
];

function isNavGroup(item: NavGroup | NavItem): item is NavGroup {
  return "items" in item;
}

export default function SuperAdminLayout() {
  const navigate = useNavigate();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [expandedGroup, setExpandedGroup] = useState<string | null>(null);

  const toggleGroup = (label: string) => {
    setExpandedGroup(prev => prev === label ? null : label);
  };

  const handleNavClick = (href: string) => {
    navigate(href);
    setSidebarOpen(false);
  };

  return (
    <div className="min-h-screen bg-admin-bg flex">
      {/* Mobile overlay */}
      <AnimatePresence>
        {sidebarOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 lg:hidden"
            onClick={() => setSidebarOpen(false)}
          />
        )}
      </AnimatePresence>

      {/* Sidebar */}
      <aside className={`fixed top-0 left-0 z-50 h-full w-[280px] bg-admin-sidebar border-r border-admin-border transition-transform duration-300 lg:translate-x-0 ${sidebarOpen ? "translate-x-0" : "-translate-x-full"}`}>
        <div className="flex items-center justify-between px-6 py-5 border-b border-admin-border/30">
          <Link to="/app" className="flex items-center gap-3">
            <img src={logo} alt="Kanisa Langu" className="h-8 w-8 object-contain" />
            <span className="text-sm font-bold text-admin-text-bright">Super Admin</span>
          </Link>
          <button onClick={() => setSidebarOpen(false)} className="lg:hidden p-1.5 rounded-lg hover:bg-admin-surface-hover text-admin-text">
            <X className="w-5 h-5" />
          </button>
        </div>

        <nav className="flex-1 overflow-y-auto px-4 py-4 space-y-6 h-[calc(100%-160px)]">
          {navSections.map((section) => (
            <div key={section.title}>
              <p className="text-[10px] font-bold uppercase tracking-widest text-admin-text/40 px-3 mb-2">{section.title}</p>
              <div className="space-y-0.5">
                {section.items.map((item) =>
                  isNavGroup(item) ? (
                    <div key={item.label}>
                      <button
                        onClick={() => toggleGroup(item.label)}
                        className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
                          expandedGroup === item.label ? "text-admin-accent bg-admin-accent/5" : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                        }`}
                      >
                        <item.icon className="w-4 h-4 flex-shrink-0" />
                        <span className="flex-1 text-left truncate">{item.label}</span>
                        <ChevronDown className={`w-3.5 h-3.5 transition-transform ${expandedGroup === item.label ? "rotate-180" : ""}`} />
                      </button>
                      <AnimatePresence>
                        {expandedGroup === item.label && (
                          <motion.div
                            initial={{ height: 0, opacity: 0 }}
                            animate={{ height: "auto", opacity: 1 }}
                            exit={{ height: 0, opacity: 0 }}
                            transition={{ duration: 0.2 }}
                            className="overflow-hidden"
                          >
                            <div className="ml-4 pl-4 border-l border-admin-border/50 mt-1 space-y-0.5">
                              {item.items.map((sub) => (
                                <button
                                  key={sub.href}
                                  onClick={() => handleNavClick(sub.href)}
                                  className={`w-full text-left px-3 py-2 rounded-lg text-[13px] transition-colors ${
                                    location.pathname === sub.href
                                      ? "text-admin-accent bg-admin-accent/5 font-medium"
                                      : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                                  }`}
                                >
                                  {sub.label}
                                </button>
                              ))}
                            </div>
                          </motion.div>
                        )}
                      </AnimatePresence>
                    </div>
                  ) : (
                    <button
                      key={item.label}
                      onClick={() => handleNavClick(item.href)}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors ${
                        location.pathname === item.href
                          ? "text-admin-accent bg-admin-accent/5"
                          : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                      }`}
                    >
                      <item.icon className="w-4 h-4 flex-shrink-0" />
                      <span className="truncate">{item.label}</span>
                    </button>
                  )
                )}
              </div>
            </div>
          ))}
        </nav>

        <div className="px-4 py-4 border-t border-admin-border/30">
          <button
            onClick={() => navigate("/app/sign-in")}
            className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-destructive hover:bg-destructive/5 transition-colors"
          >
            <LogOut className="w-4 h-4" />
            <span>Sign Out</span>
          </button>
        </div>
      </aside>

      {/* Main area */}
      <div className="flex-1 lg:ml-[280px]">
        {/* Header */}
        <header className="sticky top-0 z-30 bg-admin-header border-b border-admin-border/30 px-4 lg:px-8 py-3 flex items-center justify-between">
          <button onClick={() => setSidebarOpen(true)} className="lg:hidden p-2 rounded-xl hover:bg-admin-surface-hover text-admin-text">
            <Menu className="w-5 h-5" />
          </button>
          <div className="hidden lg:block">
            <h2 className="text-sm font-semibold text-admin-text-bright">Kanisa Langu Super Admin</h2>
          </div>
          <div className="flex items-center gap-2">
            <button className="p-2.5 rounded-xl hover:bg-admin-surface-hover text-admin-text transition-colors relative">
              <Bell className="w-4.5 h-4.5" />
              <span className="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-destructive" />
            </button>
            <button
              onClick={() => navigate(`${BASE}/profile`)}
              className="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-admin-surface-hover transition-colors"
            >
              <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-admin-accent to-amber-600 flex items-center justify-center">
                <span className="text-xs font-bold text-white">SA</span>
              </div>
              <span className="text-sm font-medium text-admin-text-bright hidden sm:block">Super Admin</span>
            </button>
          </div>
        </header>

        {/* Content */}
        <main className="p-4 lg:p-8">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
