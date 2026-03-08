import { motion } from "framer-motion";
import { Church, Users, TrendingUp, MapPin } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";
import { Link } from "react-router-dom";
import BibleWidget from "../../components/shared/BibleWidget";

const recentActivity = [
  { action: "Head parish report submitted", detail: "Msimbazi Head Parish - Q3 Report", time: "2 hours ago", color: "bg-admin-success" },
  { action: "New HP admin registered", detail: "Rev. Amina Salum - Kariakoo HP", time: "5 hours ago", color: "bg-admin-info" },
  { action: "Revenue consolidated", detail: "TZS 82M across all head parishes", time: "Yesterday", color: "bg-admin-accent" },
  { action: "Member count updated", detail: "12,450 total members in province", time: "2 days ago", color: "bg-admin-warning" },
];

const quickLinks = [
  { label: "View Head Parishes", href: "/elct/province/head-parishes", icon: Church },
  { label: "Create HP Admin", href: "/elct/province/create-hp-admin", icon: Users },
  { label: "Province Overview", href: "/elct/province/overview", icon: TrendingUp },
  { label: "Financial Summary", href: "/elct/province/financial-summary", icon: MapPin },
];

export default function ProvinceDashboard() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-admin-text-bright font-display">Province Dashboard</h1>
        <p className="text-sm text-admin-text mt-1">Coordinate head parishes and manage regional operations</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Head Parishes" value="7" change="All reporting" icon={Church} color="gold" delay={0} />
        <StatsCard title="Sub Parishes" value="34" change="+2 this quarter" trend="up" icon={MapPin} color="info" delay={0.1} />
        <StatsCard title="Total Members" value="12,450" change="+380 this month" trend="up" icon={Users} color="success" delay={0.2} />
        <StatsCard title="Province Revenue" value="TZS 280M" change="+15% vs last year" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
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

      <BibleWidget />
    </div>
  );
}
