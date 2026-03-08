import { motion } from "framer-motion";
import { Users, Building2, CreditCard, TrendingUp, Flag, BookOpen, CalendarDays, Coins } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";
import BibleWidget from "../../components/shared/BibleWidget";

const recentActivity = [
  { action: "New member registered", detail: "Maria Kimaro", time: "2 min ago", color: "bg-admin-success" },
  { action: "Revenue recorded", detail: "TZS 1,200,000 - Sadaka ya Ibada", time: "15 min ago", color: "bg-admin-accent" },
  { action: "Expense request approved", detail: "Office Supplies - TZS 150,000", time: "1 hour ago", color: "bg-admin-info" },
  { action: "Sunday service recorded", detail: "Mathayo 5:1-12", time: "3 hours ago", color: "bg-admin-warning" },
  { action: "Harambee contribution", detail: "TZS 500,000 - Church Building", time: "5 hours ago", color: "bg-admin-accent" },
  { action: "Meeting scheduled", detail: "Parish Council - Church Hall", time: "Yesterday", color: "bg-admin-info" },
];

const quickLinks = [
  { label: "Register Member", href: "/elct/head-parish/register-church-member", icon: Users },
  { label: "Record Revenue", href: "/elct/head-parish/record-revenue", icon: Coins },
  { label: "Record Service", href: "/elct/head-parish/record-sunday-service", icon: BookOpen },
  { label: "New Meeting", href: "/elct/head-parish/new-meeting", icon: CalendarDays },
  { label: "Record Harambee", href: "/elct/head-parish/record-harambee", icon: Flag },
  { label: "Financial Statement", href: "/elct/head-parish/financial-statement", icon: CreditCard },
];

export default function Dashboard() {
  return (
    <div className="space-y-8">
      {/* Welcome */}
      <div>
        <h1 className="text-2xl lg:text-3xl font-bold text-admin-text-bright font-display">
          Welcome back, Admin
        </h1>
        <p className="text-sm text-admin-text mt-1">Here's what's happening with your parish today</p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Total Members" value="2,847" change="+12 this month" trend="up" icon={Users} color="gold" delay={0} />
        <StatsCard title="Sub Parishes" value="8" change="Across 4 regions" icon={Building2} color="info" delay={0.1} />
        <StatsCard title="Monthly Revenue" value="TZS 15.2M" change="+8.3% vs last month" trend="up" icon={TrendingUp} color="success" delay={0.2} />
        <StatsCard title="Harambee Progress" value="67%" change="TZS 33.5M of 50M" icon={Flag} color="warning" delay={0.3} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Recent Activity */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
          className="lg:col-span-2 admin-card rounded-2xl p-6"
        >
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Recent Activity</h2>
          <div className="space-y-4">
            {recentActivity.map((item, i) => (
              <div key={i} className="flex items-start gap-4 group">
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

        {/* Quick Actions */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.5 }}
          className="admin-card rounded-2xl p-6"
        >
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Quick Actions</h2>
          <div className="space-y-2">
            {quickLinks.map((link) => (
              <a
                key={link.href}
                href={link.href}
                className="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-admin-surface-hover transition-colors group"
              >
                <div className="w-9 h-9 rounded-xl bg-admin-surface flex items-center justify-center group-hover:bg-admin-accent/10 transition-colors">
                  <link.icon className="w-4 h-4 text-admin-text group-hover:text-admin-accent transition-colors" />
                </div>
                <span className="text-sm text-admin-text group-hover:text-admin-text-bright transition-colors">{link.label}</span>
              </a>
            ))}
          </div>
        </motion.div>
      </div>

      {/* Revenue Chart Placeholder */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.6 }}
        className="admin-card rounded-2xl p-6"
      >
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-sm font-semibold text-admin-text-bright">Revenue Overview</h2>
          <div className="flex items-center gap-2">
            {["Weekly", "Monthly", "Yearly"].map((period) => (
              <button key={period} className={`px-3 py-1.5 rounded-lg text-[11px] font-medium transition-colors ${period === "Monthly" ? "bg-admin-accent text-admin-bg" : "text-admin-text hover:bg-admin-surface-hover"}`}>
                {period}
              </button>
            ))}
          </div>
        </div>
        {/* Chart bars */}
        <div className="flex items-end gap-3 h-48">
          {["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"].map((month, i) => {
            const height = [60, 45, 75, 50, 80, 65, 90, 55, 70, 85, 40, 95][i];
            return (
              <div key={month} className="flex-1 flex flex-col items-center gap-2">
                <motion.div
                  initial={{ height: 0 }}
                  animate={{ height: `${height}%` }}
                  transition={{ delay: 0.7 + i * 0.05, duration: 0.5, ease: "easeOut" }}
                  className="w-full rounded-t-lg bg-gradient-to-t from-admin-accent/60 to-admin-accent/20 min-h-[4px]"
                />
                <span className="text-[10px] text-admin-text/50">{month}</span>
              </div>
            );
          })}
        </div>
      </motion.div>

      {/* Bible Widget */}
      <BibleWidget />
    </div>
  );
}
