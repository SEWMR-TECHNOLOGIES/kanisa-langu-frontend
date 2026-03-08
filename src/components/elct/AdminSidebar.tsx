import { useState, useCallback } from "react";
import { Link, useLocation } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import { ChevronDown, X, User } from "lucide-react";
import logo from "../../assets/logo.png";

export interface NavItem {
  label: string;
  icon: React.ElementType;
  href?: string;
  children?: { label: string; href: string }[];
}

export interface NavSection {
  title: string;
  items: NavItem[];
}

function getItemKey(si: number, ii: number) {
  return `${si}-${ii}`;
}

function NavItemComponent({ 
  item, 
  itemKey,
  expandedKey,
  onToggle,
  onNavigate,
}: { 
  item: NavItem;
  itemKey: string;
  expandedKey: string | null;
  onToggle: (key: string) => void;
  onNavigate: () => void;
}) {
  const location = useLocation();
  const isOpen = expandedKey === itemKey;
  const isActive = item.href ? location.pathname === item.href : false;
  const hasActiveChild = item.children?.some(c => location.pathname === c.href) || false;

  if (item.href) {
    return (
      <Link
        to={item.href}
        onClick={onNavigate}
        className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-200 group
          ${isActive
            ? "bg-admin-surface-active text-admin-accent"
            : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
          }`}
      >
        <item.icon className={`w-[18px] h-[18px] flex-shrink-0 transition-colors ${isActive ? "text-admin-accent" : "text-admin-text group-hover:text-admin-text-bright"}`} />
        <span>{item.label}</span>
      </Link>
    );
  }

  return (
    <div>
      <button
        onClick={() => onToggle(itemKey)}
        className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-all duration-200 group
          ${hasActiveChild
            ? "text-admin-text-bright bg-admin-surface-hover"
            : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
          }`}
      >
        <item.icon className={`w-[18px] h-[18px] flex-shrink-0 ${hasActiveChild ? "text-admin-accent" : "text-admin-text group-hover:text-admin-text-bright"}`} />
        <span className="flex-1 text-left">{item.label}</span>
        <motion.div animate={{ rotate: isOpen ? 180 : 0 }} transition={{ duration: 0.2 }}>
          <ChevronDown className="w-3.5 h-3.5 opacity-50" />
        </motion.div>
      </button>
      <AnimatePresence>
        {isOpen && (
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
                    onClick={onNavigate}
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

interface AdminSidebarProps {
  isOpen: boolean;
  onClose: () => void;
  navigation: NavSection[];
  levelLabel: string;
  basePath: string;
}

export default function AdminSidebar({ isOpen, onClose, navigation, levelLabel, basePath }: AdminSidebarProps) {
  const location = useLocation();

  const getInitialExpanded = useCallback(() => {
    for (let si = 0; si < navigation.length; si++) {
      const section = navigation[si]!;
      for (let ii = 0; ii < section.items.length; ii++) {
        const item = section.items[ii];
        if (item?.children?.some(c => location.pathname === c.href)) {
          return getItemKey(si, ii);
        }
      }
    }
    return null;
  }, [location.pathname, navigation]);

  const [expandedKey, setExpandedKey] = useState<string | null>(getInitialExpanded);

  const handleToggle = useCallback((key: string) => {
    setExpandedKey(prev => prev === key ? null : key);
  }, []);

  const handleNavigate = useCallback(() => {
    onClose();
  }, [onClose]);

  return (
    <>
      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 lg:hidden"
            onClick={onClose}
          />
        )}
      </AnimatePresence>

      <aside
        className={`fixed top-0 left-0 h-full z-50 bg-admin-surface border-r border-admin-border w-[280px] flex flex-col transition-transform duration-300 ease-out lg:translate-x-0 ${
          isOpen ? "translate-x-0" : "-translate-x-full"
        }`}
      >
        <div className="flex items-center justify-between px-5 py-5 border-b border-admin-border">
          <Link to={basePath} onClick={handleNavigate} className="flex items-center gap-3">
            <img src={logo} alt="Kanisa Langu" className="w-9 h-9 rounded-xl" />
            <div>
              <h2 className="text-sm font-bold text-admin-text-bright tracking-tight">Kanisa Langu</h2>
              <p className="text-[10px] text-admin-text uppercase tracking-widest">{levelLabel}</p>
            </div>
          </Link>
          <button onClick={onClose} className="lg:hidden p-1.5 rounded-lg hover:bg-admin-surface-hover text-admin-text">
            <X className="w-5 h-5" />
          </button>
        </div>

        <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-6 scrollbar-thin">
          {navigation.map((section, si) => (
            <div key={section.title}>
              <p className="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-admin-text/50">
                {section.title}
              </p>
              <div className="space-y-0.5">
                {section.items.map((item, ii) => (
                  <NavItemComponent 
                    key={item.label} 
                    item={item}
                    itemKey={getItemKey(si, ii)}
                    expandedKey={expandedKey}
                    onToggle={handleToggle}
                    onNavigate={handleNavigate}
                  />
                ))}
              </div>
            </div>
          ))}
        </nav>

        <div className="p-4 border-t border-admin-border">
          <Link
            to={`${basePath}/profile`}
            onClick={handleNavigate}
            className="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-admin-surface-hover transition-colors group"
          >
            <div className="w-8 h-8 rounded-full bg-gradient-to-br from-admin-accent/20 to-admin-accent/5 flex items-center justify-center">
              <span className="text-xs font-bold text-admin-accent">A</span>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-xs font-medium text-admin-text-bright truncate group-hover:text-admin-accent transition-colors">Admin User</p>
              <p className="text-[10px] text-admin-text truncate">admin@kanisalangu.com</p>
            </div>
            <User className="w-4 h-4 text-admin-text/40 group-hover:text-admin-accent transition-colors" />
          </Link>
        </div>
      </aside>
    </>
  );
}
