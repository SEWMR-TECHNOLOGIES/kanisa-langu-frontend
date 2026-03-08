import { motion } from "framer-motion";
import { Church, Users, TrendingUp, MapPin } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";

export default function ProvinceOverview() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Province Overview</h1>
        <p className="text-sm text-admin-text mt-1">Comprehensive overview of province operations</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Head Parishes" value="7" change="All reporting" icon={Church} color="gold" delay={0} />
        <StatsCard title="Sub Parishes" value="34" change="+2 this quarter" trend="up" icon={MapPin} color="info" delay={0.1} />
        <StatsCard title="Total Members" value="12,450" change="+380 this month" trend="up" icon={Users} color="success" delay={0.2} />
        <StatsCard title="Province Revenue" value="TZS 280M" change="+15% vs last year" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.4 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Revenue by Head Parish</h2>
          <div className="space-y-4">
            {["Msimbazi", "Azania", "Uhuru", "Kariakoo", "Buguruni", "Ilala", "Kijitonyama"].map((p, i) => (
              <div key={p} className="flex items-center gap-4">
                <span className="text-xs text-admin-text w-24">{p}</span>
                <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                  <motion.div initial={{ width: 0 }} animate={{ width: `${[85, 70, 75, 92, 55, 65, 80][i]}%` }} transition={{ delay: 0.5 + i * 0.1, duration: 0.6 }} className="h-full rounded-full bg-gradient-to-r from-admin-accent to-admin-warning" />
                </div>
                <span className="text-xs font-medium text-admin-text-bright tabular-nums w-16 text-right">TZS {[55, 38, 42, 65, 22, 35, 50][i]}M</span>
              </div>
            ))}
          </div>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.5 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Members by Head Parish</h2>
          <div className="space-y-4">
            {["Msimbazi", "Azania", "Uhuru", "Kariakoo", "Buguruni", "Ilala", "Kijitonyama"].map((p, i) => (
              <div key={p} className="flex items-center gap-4">
                <span className="text-xs text-admin-text w-24">{p}</span>
                <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                  <motion.div initial={{ width: 0 }} animate={{ width: `${[89, 60, 66, 100, 45, 56, 78][i]}%` }} transition={{ delay: 0.5 + i * 0.1, duration: 0.6 }} className="h-full rounded-full bg-gradient-to-r from-admin-info to-admin-success" />
                </div>
                <span className="text-xs font-medium text-admin-text-bright tabular-nums w-16 text-right">{[2847, 1920, 2100, 3200, 1450, 1800, 2500][i].toLocaleString()}</span>
              </div>
            ))}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
