import { motion } from "framer-motion";
import { Building2, Users, MapPin, TrendingUp } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";
import { Link } from "react-router-dom";

const recentActivity = [
  { action: "New province admin created", detail: "Rev. John Mwamba - Northern Province", time: "1 hour ago", color: "bg-admin-info" },
  { action: "Head parish report submitted", detail: "Azania Head Parish - Monthly Report", time: "3 hours ago", color: "bg-admin-success" },
  { action: "Financial summary received", detail: "TZS 45M total revenue this quarter", time: "5 hours ago", color: "bg-admin-accent" },
  { action: "New head parish registered", detail: "Uhuru Head Parish - Eastern Province", time: "Yesterday", color: "bg-admin-warning" },
];

const quickLinks = [
  { label: "Create Province Admin", href: "/elct/diocese/create-province-admin", icon: Users },
  { label: "View All Provinces", href: "/elct/diocese/provinces", icon: MapPin },
  { label: "Diocese Overview", href: "/elct/diocese/overview", icon: TrendingUp },
  { label: "Manage Admins", href: "/elct/diocese/admins", icon: Building2 },
];

export default function DioceseDashboard() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-admin-text-bright font-display">Diocese Dashboard</h1>
        <p className="text-sm text-admin-text mt-1">Overview of all provinces and operations under your diocese</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Total Provinces" value="6" change="All active" icon={MapPin} color="info" delay={0} />
        <StatsCard title="Head Parishes" value="42" change="+3 this year" trend="up" icon={Building2} color="gold" delay={0.1} />
        <StatsCard title="Total Members" value="58,320" change="+1,240 this quarter" trend="up" icon={Users} color="success" delay={0.2} />
        <StatsCard title="Annual Revenue" value="TZS 1.2B" change="+12% vs last year" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.4 }} className="lg:col-span-2 admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Recent Activity</h2>
          <div className="space-y-4">
            {recentActivity.map((item, i) => (
              <div key={i} className="flex items-start gap-4">
                <div className={`w-2 h-2 rounded-full ${item.color} mt-2 flex-shrink-0`} />
                <div className="flex-1 min-w-0">
                  <p className="text-sm text-admin-text-bright">{item.action}</p>
                  <p className="text-xs text-admin-text mt-0.5">{item.detail}</p>
                </div>
                <span className="text-[11px] text-admin-text/50 flex-shrink-0">{item.time}</span>
              </div>
            ))}
          </div>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.5 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Quick Actions</h2>
          <div className="space-y-2">
            {quickLinks.map((link) => (
              <Link key={link.href} to={link.href}
                className="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-admin-surface-hover transition-colors group">
                <div className="w-9 h-9 rounded-xl bg-admin-surface-hover flex items-center justify-center group-hover:bg-admin-accent/10 transition-colors">
                  <link.icon className="w-4 h-4 text-admin-text group-hover:text-admin-accent transition-colors" />
                </div>
                <span className="text-sm text-admin-text group-hover:text-admin-text-bright transition-colors">{link.label}</span>
              </Link>
            ))}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
