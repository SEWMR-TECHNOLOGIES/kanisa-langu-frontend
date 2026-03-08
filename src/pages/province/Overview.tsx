import { motion } from "framer-motion";
import { Church, Users, TrendingUp } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";

export default function ProvinceOverview() {
  const revenueData = [
    { name: "Msimbazi", amount: 55, pct: 85 },
    { name: "Azania", amount: 38, pct: 70 },
    { name: "Uhuru", amount: 42, pct: 75 },
    { name: "Kariakoo", amount: 65, pct: 92 },
    { name: "Buguruni", amount: 22, pct: 55 },
    { name: "Ilala", amount: 35, pct: 65 },
    { name: "Kijitonyama", amount: 50, pct: 80 },
  ];
  const memberData = [
    { name: "Msimbazi", count: 2847, pct: 89 },
    { name: "Azania", count: 1920, pct: 60 },
    { name: "Uhuru", count: 2100, pct: 66 },
    { name: "Kariakoo", count: 3200, pct: 100 },
    { name: "Buguruni", count: 1450, pct: 45 },
    { name: "Ilala", count: 1800, pct: 56 },
    { name: "Kijitonyama", count: 2500, pct: 78 },
  ];

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Province Overview</h1>
        <p className="text-sm text-admin-text mt-1">Comprehensive overview of province operations</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Head Parishes" value="7" change="All reporting" icon={Church} color="gold" delay={0} />
        <StatsCard title="Sub Parishes" value="34" change="+2 this quarter" trend="up" icon={Church} color="info" delay={0.1} />
        <StatsCard title="Total Members" value="12,450" change="+380 this month" trend="up" icon={Users} color="success" delay={0.2} />
        <StatsCard title="Province Revenue" value="TZS 280M" change="+15% vs last year" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.4 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Revenue by Head Parish</h2>
          <div className="space-y-4">
            {revenueData.map((p, i) => (
              <div key={p.name} className="flex items-center gap-4">
                <span className="text-xs text-admin-text w-24">{p.name}</span>
                <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                  <motion.div initial={{ width: 0 }} animate={{ width: `${p.pct}%` }} transition={{ delay: 0.5 + i * 0.1, duration: 0.6 }} className="h-full rounded-full bg-gradient-to-r from-admin-accent to-admin-warning" />
                </div>
                <span className="text-xs font-medium text-admin-text-bright tabular-nums w-16 text-right">TZS {p.amount}M</span>
              </div>
            ))}
          </div>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.5 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Members by Head Parish</h2>
          <div className="space-y-4">
            {memberData.map((p, i) => (
              <div key={p.name} className="flex items-center gap-4">
                <span className="text-xs text-admin-text w-24">{p.name}</span>
                <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                  <motion.div initial={{ width: 0 }} animate={{ width: `${p.pct}%` }} transition={{ delay: 0.5 + i * 0.1, duration: 0.6 }} className="h-full rounded-full bg-gradient-to-r from-admin-info to-admin-success" />
                </div>
                <span className="text-xs font-medium text-admin-text-bright tabular-nums w-16 text-right">{p.count.toLocaleString()}</span>
              </div>
            ))}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
