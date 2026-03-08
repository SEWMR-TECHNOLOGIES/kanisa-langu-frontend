import { useState } from "react";
import { NavLink, useLocation } from "react-router-dom";
import { useAuth } from "@/contexts/AuthContext";
import {
  LayoutDashboard, Globe, MapPin, Building2, Database, Map,
  Tag, Briefcase, Music, CreditCard, BarChart3, LogOut,
  ChevronDown, Church, X,
} from "lucide-react";
import { cn } from "@/lib/utils";

interface SidebarProps {
  open: boolean;
  onClose: () => void;
}

interface NavGroup {
  label: string;
  items: NavItem[];
}

interface NavItem {
  label: string;
  icon: React.ReactNode;
  href?: string;
  children?: { label: string; href: string }[];
}

const navigation: NavGroup[] = [
  {
    label: "Home",
    items: [
      { label: "Dashboard", icon: <LayoutDashboard className="h-5 w-5" />, href: "/" },
    ],
  },
  {
    label: "Administration",
    items: [
      {
        label: "Diocese", icon: <Globe className="h-5 w-5" />,
        children: [
          { label: "Register Diocese", href: "/diocese/register" },
          { label: "Manage Dioceses", href: "/diocese/manage" },
          { label: "Create Admins", href: "/diocese/create-admin" },
          { label: "Admins List", href: "/diocese/admins" },
        ],
      },
      {
        label: "Provinces", icon: <MapPin className="h-5 w-5" />,
        children: [
          { label: "Register Province", href: "/provinces/register" },
          { label: "Manage Provinces", href: "/provinces/manage" },
        ],
      },
      {
        label: "Head Parishes", icon: <Building2 className="h-5 w-5" />,
        children: [
          { label: "Register Head Parish", href: "/head-parishes/register" },
          { label: "Manage Head Parishes", href: "/head-parishes/manage" },
        ],
      },
    ],
  },
  {
    label: "Data",
    items: [
      {
        label: "Banks", icon: <Database className="h-5 w-5" />,
        children: [
          { label: "Register Bank", href: "/banks/register" },
          { label: "Manage Banks", href: "/banks/manage" },
        ],
      },
      {
        label: "Locations", icon: <Map className="h-5 w-5" />,
        children: [
          { label: "Register Regions", href: "/locations/register-regions" },
          { label: "Manage Regions", href: "/locations/manage-regions" },
          { label: "Register Districts", href: "/locations/register-districts" },
          { label: "Manage Districts", href: "/locations/manage-districts" },
        ],
      },
      {
        label: "Titles", icon: <Tag className="h-5 w-5" />,
        children: [
          { label: "Add Title", href: "/data/add-titles" },
          { label: "Manage Titles", href: "/data/manage-titles" },
        ],
      },
      {
        label: "Occupations", icon: <Briefcase className="h-5 w-5" />,
        children: [
          { label: "Add Occupation", href: "/data/add-occupations" },
          { label: "Manage Occupations", href: "/data/manage-occupations" },
        ],
      },
      {
        label: "Praise Songs", icon: <Music className="h-5 w-5" />,
        children: [
          { label: "Register Praise Song", href: "/data/register-praise-song" },
          { label: "Manage Praise Songs", href: "/data/manage-praise-songs" },
        ],
      },
    ],
  },
  {
    label: "Payments",
    items: [
      {
        label: "Payments", icon: <CreditCard className="h-5 w-5" />,
        children: [
          { label: "Manage Payments", href: "/payments/manage" },
          { label: "Payment Reports", href: "/payments/reports" },
        ],
      },
    ],
  },
  {
    label: "Reports",
    items: [
      {
        label: "Reports", icon: <BarChart3 className="h-5 w-5" />,
        children: [
          { label: "Sales", href: "/reports/sales" },
          { label: "SMS Usage", href: "/reports/sms-usage" },
        ],
      },
    ],
  },
];

function CollapsibleItem({ item }: { item: NavItem }) {
  const location = useLocation();
  const isChildActive = item.children?.some((c) => location.pathname === c.href) ?? false;
  const [open, setOpen] = useState(isChildActive);

  if (!item.children) {
    return (
      <NavLink
        to={item.href!}
        className={({ isActive }) =>
          cn(
            "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors",
            isActive
              ? "bg-sidebar-primary text-sidebar-primary-foreground"
              : "text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
          )
        }
      >
        {item.icon}
        <span>{item.label}</span>
      </NavLink>
    );
  }

  return (
    <div>
      <button
        onClick={() => setOpen(!open)}
        className={cn(
          "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors w-full",
          isChildActive
            ? "text-sidebar-primary"
            : "text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
        )}
      >
        {item.icon}
        <span className="flex-1 text-left">{item.label}</span>
        <ChevronDown className={cn("h-4 w-4 transition-transform", open && "rotate-180")} />
      </button>
      {open && (
        <div className="ml-8 mt-1 space-y-0.5">
          {item.children.map((child) => (
            <NavLink
              key={child.href}
              to={child.href}
              className={({ isActive }) =>
                cn(
                  "block px-3 py-2 rounded-md text-sm transition-colors",
                  isActive
                    ? "bg-sidebar-primary text-sidebar-primary-foreground"
                    : "text-muted-foreground hover:text-sidebar-foreground hover:bg-sidebar-accent"
                )
              }
            >
              {child.label}
            </NavLink>
          ))}
        </div>
      )}
    </div>
  );
}

export default function AppSidebar({ open, onClose }: SidebarProps) {
  const { logout } = useAuth();

  return (
    <>
      {open && (
        <div className="fixed inset-0 bg-black/50 z-40 xl:hidden" onClick={onClose} />
      )}
      <aside
        className={cn(
          "fixed top-0 left-0 z-50 h-full w-[270px] bg-sidebar border-r border-sidebar-border flex flex-col transition-transform duration-200",
          "xl:translate-x-0",
          open ? "translate-x-0" : "-translate-x-full"
        )}
      >
        <div className="flex items-center justify-between h-[70px] px-6 border-b border-sidebar-border">
          <div className="flex items-center gap-2">
            <Church className="h-7 w-7 text-primary" />
            <span className="font-bold text-lg text-sidebar-foreground">Kanisa Langu</span>
          </div>
          <button onClick={onClose} className="xl:hidden text-muted-foreground hover:text-foreground">
            <X className="h-5 w-5" />
          </button>
        </div>

        <nav className="flex-1 overflow-y-auto px-4 py-4 space-y-6">
          {navigation.map((group) => (
            <div key={group.label}>
              <p className="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2 px-3">
                {group.label}
              </p>
              <div className="space-y-0.5">
                {group.items.map((item) => (
                  <CollapsibleItem key={item.label} item={item} />
                ))}
              </div>
            </div>
          ))}
        </nav>

        <div className="p-4 border-t border-sidebar-border">
          <button
            onClick={logout}
            className="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-destructive hover:bg-destructive/10 transition-colors w-full"
          >
            <LogOut className="h-5 w-5" />
            <span>Sign Out</span>
          </button>
        </div>
      </aside>
    </>
  );
}
