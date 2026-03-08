import { motion } from "framer-motion";
import { Building2, Users, TrendingUp, MapPin } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";

export default function DioceseOverview() {
  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Diocese Overview</h1>
        <p className="text-sm text-admin-text mt-1">Comprehensive overview of diocese operations</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <StatsCard title="Provinces" value="6" change="All active" icon={MapPin} color="info" delay={0} />
        <StatsCard title="Head Parishes" value="42" change="+3 this year" trend="up" icon={Building2} color="gold" delay={0.1} />
        <StatsCard title="Total Members" value="58,320" change="+1,240 this quarter" trend="up" icon={Users} color="success" delay={0.2} />
        <StatsCard title="Annual Revenue" value="TZS 1.2B" change="+12% vs last year" trend="up" icon={TrendingUp} color="warning" delay={0.3} />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.4 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Revenue by Province</h2>
          <div className="space-y-4">
            {["Northern", "Eastern", "Southern", "Western", "Central", "Lake"].map((p, i) => (
              <div key={p} className="flex items-center gap-4">
                <span className="text-xs text-admin-text w-20">{p}</span>
                <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                  <motion.div
                    initial={{ width: 0 }}
                    animate={{ width: `${[82, 65, 90, 55, 72, 78][i]}%` }}
                    transition={{ delay: 0.5 + i * 0.1, duration: 0.6 }}
                    className="h-full rounded-full bg-gradient-to-r from-admin-accent to-admin-warning"
                  />
                </div>
                <span className="text-xs font-medium text-admin-text-bright tabular-nums w-16 text-right">TZS {[200, 150, 280, 120, 180, 220][i]}M</span>
              </div>
            ))}
          </div>
        </motion.div>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.5 }} className="admin-card rounded-2xl p-6">
          <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Members by Province</h2>
          <div className="space-y-4">
            {["Northern", "Eastern", "Southern", "Western", "Central", "Lake"].map((p, i) => (
              <div key={p} className="flex items-center gap-4">
                <span className="text-xs text-admin-text w-20">{p}</span>
                <div className="flex-1 h-2.5 rounded-full bg-admin-surface-hover overflow-hidden">
                  <motion.div
                    initial={{ width: 0 }}
                    animate={{ width: `${[75, 55, 92, 42, 60, 70][i]}%` }}
                    transition={{ delay: 0.5 + i * 0.1, duration: 0.6 }}
                    className="h-full rounded-full bg-gradient-to-r from-admin-info to-admin-success"
                  />
                </div>
                <span className="text-xs font-medium text-admin-text-bright tabular-nums w-16 text-right">{[12450, 8900, 15200, 6700, 9800, 11300][i].toLocaleString()}</span>
              </div>
            ))}
          </div>
        </motion.div>
      </div>
    </div>
  );
}
