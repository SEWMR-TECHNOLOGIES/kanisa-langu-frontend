import { motion } from "framer-motion";
import { Users, Home, CalendarDays, TrendingUp } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";
import { Link } from "react-router-dom";
import BibleWidget from "../../components/shared/BibleWidget";

const recentActivity = [
  { action: "Household visit completed", detail: "Familia ya Mwanga - Home fellowship", time: "1 hour ago", color: "bg-admin-success" },
  { action: "Meeting scheduled", detail: "Community prayer meeting - Saturday", time: "3 hours ago", color: "bg-admin-info" },
  { action: "New member added", detail: "Peter Kimaro - Joined from SP transfer", time: "Yesterday", color: "bg-admin-accent" },
  { action: "Contribution received", detail: "TZS 45,000 - Community fund", time: "2 days ago", color: "bg-admin-warning" },
];

const quickLinks = [
  { label: "View Members", href: "/elct/community/members", icon: Users },
  { label: "View Households", href: "/elct/community/households", icon: Home },
  { label: "Schedule Meeting", href: "/elct/community/schedule-meeting", icon: CalendarDays },
  { label: "Send Notification", href: "/elct/community/send-notification", icon: TrendingUp },
];

export default function CommunityDashboard() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-admin-text-bright font-display">Community Dashboard</h1>
        <p className="text-sm text-admin-text mt-1">Manage neighborhood fellowship and grassroots engagement</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Households" value="68" change="All mapped" icon={Home} color="gold" delay={0} />
        <StatsCard title="Members" value="124" change="+4 this month" trend="up" icon={Users} color="info" delay={0.1} />
        <StatsCard title="Meetings/Month" value="8" change="On schedule" icon={CalendarDays} color="success" delay={0.2} />
        <StatsCard title="Contributions" value="TZS 450K" change="+8% this month" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
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
