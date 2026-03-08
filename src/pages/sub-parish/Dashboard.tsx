import { motion } from "framer-motion";
import { Users, Home, TrendingUp, BookOpen } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";
import { Link } from "react-router-dom";

const recentActivity = [
  { action: "New member registered", detail: "Anna Mushi - Joined community A", time: "30 min ago", color: "bg-admin-success" },
  { action: "Revenue recorded", detail: "TZS 350,000 - Sadaka ya Ibada", time: "2 hours ago", color: "bg-admin-accent" },
  { action: "Harambee contribution", detail: "TZS 120,000 - Church Building Fund", time: "4 hours ago", color: "bg-admin-warning" },
  { action: "Sunday service recorded", detail: "320 attendance - Main Service", time: "Yesterday", color: "bg-admin-info" },
  { action: "Envelope contribution", detail: "TZS 85,000 - Monthly envelope", time: "2 days ago", color: "bg-admin-accent" },
];

const quickLinks = [
  { label: "Register Member", href: "/elct/sub-parish/register-church-member", icon: Users },
  { label: "Record Revenue", href: "/elct/sub-parish/record-revenue", icon: TrendingUp },
  { label: "Record Harambee", href: "/elct/sub-parish/record-harambee", icon: Home },
  { label: "View Services", href: "/elct/sub-parish/services", icon: BookOpen },
];

export default function SubParishDashboard() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-admin-text-bright font-display">Sub Parish Dashboard</h1>
        <p className="text-sm text-admin-text mt-1">Manage your congregation and day-to-day worship activities</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Members" value="485" change="+8 this month" trend="up" icon={Users} color="gold" delay={0} />
        <StatsCard title="Communities" value="4" change="All active" icon={Home} color="info" delay={0.1} />
        <StatsCard title="Weekly Attendance" value="320" change="+5% vs last week" trend="up" icon={BookOpen} color="success" delay={0.2} />
        <StatsCard title="Monthly Revenue" value="TZS 2.8M" change="+10% vs last month" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
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
