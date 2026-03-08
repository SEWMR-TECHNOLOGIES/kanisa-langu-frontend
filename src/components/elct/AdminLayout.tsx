import { useState } from "react";
import { Outlet, useLocation } from "react-router-dom";
import { motion } from "framer-motion";
import { Menu, Search, Bell, ChevronRight } from "lucide-react";
import AdminSidebar from "./AdminSidebar";
import type { NavSection } from "./AdminSidebar";

interface AdminLayoutProps {
  navigation: NavSection[];
  levelLabel: string;
  basePath: string;
}

export default function AdminLayout({ navigation, levelLabel, basePath }: AdminLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const location = useLocation();

  const pathSegments = location.pathname.split("/").filter(Boolean);
  const breadcrumbs = pathSegments.map((seg, i) => ({
    label: seg.replace(/-/g, " ").replace(/\b\w/g, c => c.toUpperCase()),
    path: "/" + pathSegments.slice(0, i + 1).join("/"),
  }));

  return (
    <div className="min-h-screen bg-admin-bg">
      <AdminSidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} navigation={navigation} levelLabel={levelLabel} basePath={basePath} />

      <div className="lg:ml-[280px] min-h-screen flex flex-col">
        <header className="sticky top-0 z-30 bg-admin-header border-b border-admin-border">
          <div className="flex items-center justify-between px-4 lg:px-8 h-16">
            <div className="flex items-center gap-4">
              <button onClick={() => setSidebarOpen(true)} className="lg:hidden p-2 rounded-lg hover:bg-admin-surface-hover text-admin-text">
                <Menu className="w-5 h-5" />
              </button>
              <div className="hidden sm:flex items-center gap-1.5 text-[12px]">
                {breadcrumbs.map((crumb, i) => (
                  <span key={crumb.path} className="flex items-center gap-1.5">
                    {i > 0 && <ChevronRight className="w-3 h-3 text-admin-text/30" />}
                    <span className={i === breadcrumbs.length - 1 ? "text-admin-text-bright font-medium" : "text-admin-text/60"}>
                      {crumb.label}
                    </span>
                  </span>
                ))}
              </div>
            </div>

            <div className="flex items-center gap-3">
              <div className="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-admin-surface-hover border border-admin-border w-64">
                <Search className="w-4 h-4 text-admin-text/50" />
                <input type="text" placeholder="Search..." className="bg-transparent text-xs text-admin-text-bright placeholder:text-admin-text/40 outline-none w-full" />
              </div>
              <button className="relative p-2.5 rounded-xl hover:bg-admin-surface-hover text-admin-text transition-colors">
                <Bell className="w-[18px] h-[18px]" />
                <span className="absolute top-2 right-2 w-2 h-2 rounded-full bg-admin-accent" />
              </button>
              <div className="w-8 h-8 rounded-full bg-gradient-to-br from-admin-accent to-amber-600 flex items-center justify-center cursor-pointer">
                <span className="text-xs font-bold text-white">A</span>
              </div>
            </div>
          </div>
        </header>

        <main className="flex-1 p-4 lg:p-8">
          <motion.div key={location.pathname} initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.3, ease: "easeOut" }}>
            <Outlet />
          </motion.div>
        </main>
      </div>
    </div>
  );
}
