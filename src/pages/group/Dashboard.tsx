import { motion } from "framer-motion";
import { Users, BookOpen, Coins, Flag, TrendingUp, ArrowUpRight } from "lucide-react";

const stats = [
  { label: "Group Members", value: "45", icon: Users, change: "+3 this month", trend: "up" as const },
  { label: "Services", value: "12", icon: BookOpen, change: "This quarter", trend: "up" as const },
  { label: "Revenue (TZS)", value: "2,450,000", icon: Coins, change: "+15% vs last month", trend: "up" as const },
  { label: "Harambee Progress", value: "67%", icon: Flag, change: "On track", trend: "up" as const },
];

export default function GroupDashboard() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Group Dashboard</h1>
        <p className="text-sm text-admin-text mt-1">Welcome back! Here's your group overview.</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {stats.map((stat, i) => (
          <motion.div
            key={stat.label}
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.08, duration: 0.3 }}
            className="admin-card rounded-2xl p-5 group hover:border-admin-accent/30 transition-colors"
          >
            <div className="flex items-start justify-between mb-3">
              <div className="p-2.5 rounded-xl bg-admin-accent/10">
                <stat.icon size={18} className="text-admin-accent" />
              </div>
              <span className="flex items-center gap-1 text-xs font-medium text-admin-success">
                <TrendingUp size={12} />
                <ArrowUpRight size={10} />
              </span>
            </div>
            <p className="text-2xl font-bold text-admin-text-bright tabular-nums">{stat.value}</p>
            <p className="text-xs text-admin-text mt-1">{stat.label}</p>
            <p className="text-[10px] text-admin-text/60 mt-0.5">{stat.change}</p>
          </motion.div>
        ))}
      </div>
    </div>
  );
}
