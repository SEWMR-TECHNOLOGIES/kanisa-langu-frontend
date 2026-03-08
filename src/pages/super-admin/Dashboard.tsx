import { motion } from "framer-motion";
import { Globe, MapPin, Building2, Database, Users, CreditCard, BarChart3, TrendingUp, ArrowUpRight, Activity, Zap } from "lucide-react";
import { Link } from "react-router-dom";

const stats = [
  { label: "Dioceses", value: "26", change: "+2", trend: "up" as const, icon: Globe, gradient: "from-admin-accent/20 to-admin-accent/5", iconColor: "text-admin-accent", glow: "shadow-[0_0_40px_-10px_hsla(42,92%,56%,0.15)]" },
  { label: "Provinces", value: "42", change: "+5", trend: "up" as const, icon: MapPin, gradient: "from-admin-info/20 to-admin-info/5", iconColor: "text-admin-info", glow: "shadow-[0_0_40px_-10px_hsla(210,80%,56%,0.15)]" },
  { label: "Head Parishes", value: "187", change: "+12", trend: "up" as const, icon: Building2, gradient: "from-admin-success/20 to-admin-success/5", iconColor: "text-admin-success", glow: "shadow-[0_0_40px_-10px_hsla(152,69%,48%,0.15)]" },
  { label: "Banks", value: "14", change: "+1", trend: "up" as const, icon: Database, gradient: "from-admin-warning/20 to-admin-warning/5", iconColor: "text-admin-warning", glow: "shadow-[0_0_40px_-10px_hsla(38,92%,50%,0.15)]" },
  { label: "Admins", value: "53", change: "+8", trend: "up" as const, icon: Users, gradient: "from-admin-accent/20 to-admin-accent/5", iconColor: "text-admin-accent", glow: "shadow-[0_0_40px_-10px_hsla(42,92%,56%,0.15)]" },
  { label: "Payments", value: "1,240", change: "+18%", trend: "up" as const, icon: CreditCard, gradient: "from-admin-info/20 to-admin-info/5", iconColor: "text-admin-info", glow: "shadow-[0_0_40px_-10px_hsla(210,80%,56%,0.15)]" },
];

const quickActions = [
  { label: "Register Diocese", href: "/app/register-diocese", icon: Globe, desc: "Add new diocese" },
  { label: "Register Province", href: "/app/register-province", icon: MapPin, desc: "Add new province" },
  { label: "Register Head Parish", href: "/app/register-head-parish", icon: Building2, desc: "Add new parish" },
  { label: "View Reports", href: "/app/sales-report", icon: BarChart3, desc: "Analytics & insights" },
];

const recentActivity = [
  { action: "New diocese registered", detail: "Diocese of Moshi", time: "2h ago", icon: Globe },
  { action: "Admin account created", detail: "john@example.com", time: "5h ago", icon: Users },
  { action: "Payment processed", detail: "TZS 2,500,000", time: "8h ago", icon: CreditCard },
  { action: "Province updated", detail: "Arusha Province", time: "1d ago", icon: MapPin },
];

export default function SuperAdminDashboard() {
  const now = new Date();
  const greeting = now.getHours() < 12 ? "Good morning" : now.getHours() < 17 ? "Good afternoon" : "Good evening";

  return (
    <div className="space-y-6">
      {/* Hero greeting */}
      <motion.div
        initial={{ opacity: 0, y: 12 }}
        animate={{ opacity: 1, y: 0 }}
        className="flex items-center justify-between"
      >
        <div>
          <h1 className="text-2xl font-bold text-admin-text-bright font-display">{greeting}, Super Admin</h1>
          <p className="text-sm text-admin-text/60 mt-1">Here's what's happening across Kanisa Langu</p>
        </div>
        <div className="hidden md:flex items-center gap-2 px-4 py-2 rounded-xl bg-admin-success/10 border border-admin-success/20">
          <Activity className="w-3.5 h-3.5 text-admin-success" />
          <span className="text-xs font-medium text-admin-success">System Online</span>
        </div>
      </motion.div>

      {/* Stats grid */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        {stats.map((stat, i) => (
          <motion.div
            key={stat.label}
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.04 }}
            className={`admin-card rounded-2xl p-4 ${stat.glow} hover:scale-[1.02] transition-transform duration-200 group`}
          >
            <div className="flex items-center justify-between mb-3">
              <div className={`w-9 h-9 rounded-xl bg-gradient-to-br ${stat.gradient} flex items-center justify-center`}>
                <stat.icon className={`w-4.5 h-4.5 ${stat.iconColor}`} />
              </div>
              <span className="flex items-center gap-0.5 text-[10px] font-semibold text-admin-success bg-admin-success/10 px-1.5 py-0.5 rounded-full">
                <TrendingUp className="w-2.5 h-2.5" />
                {stat.change}
              </span>
            </div>
            <p className="text-2xl font-bold text-admin-text-bright font-display tabular-nums leading-none">{stat.value}</p>
            <p className="text-[11px] text-admin-text/50 mt-1.5 font-medium">{stat.label}</p>
          </motion.div>
        ))}
      </div>

      <div className="grid lg:grid-cols-5 gap-4">
        {/* Quick actions */}
        <motion.div
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.25 }}
          className="lg:col-span-3 admin-card rounded-2xl p-5"
        >
          <div className="flex items-center gap-2 mb-4">
            <Zap className="w-4 h-4 text-admin-accent" />
            <h2 className="text-sm font-bold text-admin-text-bright font-display">Quick Actions</h2>
          </div>
          <div className="grid grid-cols-2 gap-3">
            {quickActions.map((action) => (
              <Link
                key={action.label}
                to={action.href}
                className="flex items-center gap-3 p-3.5 rounded-xl bg-admin-bg border border-admin-border/20 hover:border-admin-accent/30 hover:bg-admin-accent/5 transition-all group"
              >
                <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-admin-accent/15 to-admin-accent/5 flex items-center justify-center shrink-0 group-hover:from-admin-accent/25 transition-all">
                  <action.icon className="w-4.5 h-4.5 text-admin-accent" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-admin-text-bright">{action.label}</p>
                  <p className="text-[11px] text-admin-text/40">{action.desc}</p>
                </div>
                <ArrowUpRight className="w-3.5 h-3.5 text-admin-text/20 group-hover:text-admin-accent transition-colors shrink-0" />
              </Link>
            ))}
          </div>
        </motion.div>

        {/* Recent activity */}
        <motion.div
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.3 }}
          className="lg:col-span-2 admin-card rounded-2xl p-5"
        >
          <h2 className="text-sm font-bold text-admin-text-bright font-display mb-4">Recent Activity</h2>
          <div className="space-y-1">
            {recentActivity.map((item, i) => (
              <div key={i} className="flex items-start gap-3 p-2.5 rounded-lg hover:bg-admin-bg/50 transition-colors">
                <div className="w-8 h-8 rounded-lg bg-admin-accent/10 flex items-center justify-center shrink-0 mt-0.5">
                  <item.icon className="w-3.5 h-3.5 text-admin-accent" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-xs font-medium text-admin-text-bright">{item.action}</p>
                  <p className="text-[11px] text-admin-text/40 truncate">{item.detail}</p>
                </div>
                <span className="text-[10px] text-admin-text/30 shrink-0">{item.time}</span>
              </div>
            ))}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
