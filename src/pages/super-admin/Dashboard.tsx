import { motion } from "framer-motion";
import { Globe, MapPin, Building2, Database, Users, CreditCard, BarChart3, Clock } from "lucide-react";

const stats = [
  { label: "Dioceses", value: "26", icon: Globe, color: "text-admin-accent" },
  { label: "Provinces", value: "42", icon: MapPin, color: "text-admin-info" },
  { label: "Head Parishes", value: "187", icon: Building2, color: "text-admin-success" },
  { label: "Registered Banks", value: "14", icon: Database, color: "text-admin-warning" },
  { label: "Total Admins", value: "53", icon: Users, color: "text-admin-accent" },
  { label: "Active Payments", value: "1,240", icon: CreditCard, color: "text-admin-info" },
];

export default function SuperAdminDashboard() {
  const now = new Date();
  const formatted = now.toLocaleDateString("en-GB", {
    weekday: "long", day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit",
  });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Welcome, Super Admin!</h1>
        <p className="text-sm text-admin-text mt-1">Kanisa Langu Management Dashboard</p>
      </div>

      {/* Welcome card */}
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        className="admin-card rounded-2xl p-6"
      >
        <div className="flex items-center gap-3 text-sm text-admin-text">
          <Clock className="w-4 h-4 text-admin-accent" />
          <span>Current time: <strong className="text-admin-text-bright">{formatted}</strong></span>
        </div>
        <p className="text-sm text-admin-text mt-2">
          Last logged in on <strong className="text-admin-text-bright">Saturday, 08 Mar 2026 09:15</strong>
        </p>
      </motion.div>

      {/* Stats grid */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {stats.map((stat, i) => (
          <motion.div
            key={stat.label}
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.05 }}
            className="admin-card rounded-2xl p-4 text-center"
          >
            <stat.icon className={`w-6 h-6 mx-auto mb-2 ${stat.color}`} />
            <p className="text-2xl font-bold text-admin-text-bright font-display">{stat.value}</p>
            <p className="text-xs text-admin-text mt-1">{stat.label}</p>
          </motion.div>
        ))}
      </div>

      {/* Quick actions */}
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3 }}
        className="admin-card rounded-2xl p-6"
      >
        <h2 className="text-sm font-bold text-admin-text-bright mb-4 font-display">Quick Actions</h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
          {[
            { label: "Register Diocese", href: "/app/register-diocese", icon: Globe },
            { label: "Register Province", href: "/app/register-province", icon: MapPin },
            { label: "Register Head Parish", href: "/app/register-head-parish", icon: Building2 },
            { label: "View Reports", href: "/app/sales-report", icon: BarChart3 },
          ].map((action) => (
            <a
              key={action.label}
              href={action.href}
              className="flex items-center gap-3 p-3 rounded-xl bg-admin-bg border border-admin-border/30 hover:border-admin-accent/30 hover:bg-admin-accent/5 transition-all group"
            >
              <action.icon className="w-4 h-4 text-admin-text group-hover:text-admin-accent transition-colors" />
              <span className="text-sm font-medium text-admin-text-bright">{action.label}</span>
            </a>
          ))}
        </div>
      </motion.div>
    </div>
  );
}
