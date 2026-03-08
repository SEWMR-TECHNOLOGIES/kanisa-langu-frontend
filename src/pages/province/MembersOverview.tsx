import { motion } from "framer-motion";
import { Users, TrendingUp } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";

export default function ProvinceMembersOverview() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Members Overview</h1>
        <p className="text-sm text-admin-text mt-1">Province-wide membership statistics</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
        <StatsCard title="Total Members" value="12,450" change="+380 this month" trend="up" icon={Users} color="gold" delay={0} />
        <StatsCard title="Active Members" value="11,200" change="90% active rate" icon={Users} color="success" delay={0.1} />
        <StatsCard title="New Members" value="380" change="This month" trend="up" icon={TrendingUp} color="info" delay={0.2} />
      </div>

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }} className="admin-card rounded-2xl p-6">
        <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Members Distribution</h2>
        <div className="space-y-4">
          {[
            { name: "Msimbazi", count: 2847, pct: 89 },
            { name: "Azania", count: 1920, pct: 60 },
            { name: "Uhuru", count: 2100, pct: 66 },
            { name: "Kariakoo", count: 3200, pct: 100 },
            { name: "Buguruni", count: 1450, pct: 45 },
            { name: "Ilala", count: 1800, pct: 56 },
            { name: "Kijitonyama", count: 2500, pct: 78 },
          ].map((hp, i) => (
            <div key={hp.name} className="flex items-center gap-4">
              <span className="text-xs text-admin-text w-24">{hp.name}</span>
              <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                <motion.div initial={{ width: 0 }} animate={{ width: `${hp.pct}%` }} transition={{ delay: 0.4 + i * 0.1, duration: 0.6 }} className="h-full rounded-full bg-gradient-to-r from-admin-info to-admin-success" />
              </div>
              <span className="text-xs font-medium text-admin-text-bright tabular-nums">{hp.count.toLocaleString()}</span>
            </div>
          ))}
        </div>
      </motion.div>
    </div>
  );
}
