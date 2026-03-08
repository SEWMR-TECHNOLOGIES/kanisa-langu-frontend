import { motion } from "framer-motion";
import { TrendingUp, TrendingDown, Wallet } from "lucide-react";
import StatsCard from "../../components/head-parish/StatsCard";

export default function ProvinceFinancialSummary() {
  const items = [
    { label: "Sadaka ya Ibada", amount: "TZS 85,000,000", percentage: 30 },
    { label: "Zaka", amount: "TZS 62,000,000", percentage: 22 },
    { label: "Harambee", amount: "TZS 55,000,000", percentage: 20 },
    { label: "Michango Maalum", amount: "TZS 42,000,000", percentage: 15 },
    { label: "Others", amount: "TZS 36,000,000", percentage: 13 },
  ];

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Financial Summary</h1>
        <p className="text-sm text-admin-text mt-1">Province-wide financial performance</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
        <StatsCard title="Total Revenue" value="TZS 280M" change="+15% vs last year" trend="up" icon={TrendingUp} color="success" delay={0} />
        <StatsCard title="Total Expenses" value="TZS 180M" change="+8% vs last year" trend="up" icon={TrendingDown} color="warning" delay={0.1} />
        <StatsCard title="Net Income" value="TZS 100M" change="+28% vs last year" trend="up" icon={Wallet} color="gold" delay={0.2} />
      </div>

      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }} className="admin-card rounded-2xl p-6">
        <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Revenue Breakdown</h2>
        <div className="space-y-4">
          {items.map((item, i) => (
            <div key={item.label} className="flex items-center gap-4">
              <span className="text-xs text-admin-text w-32">{item.label}</span>
              <div className="flex-1 h-3 rounded-full bg-admin-surface-hover overflow-hidden">
                <motion.div
                  initial={{ width: 0 }}
                  animate={{ width: `${item.percentage * 3}%` }}
                  transition={{ delay: 0.4 + i * 0.1, duration: 0.6 }}
                  className="h-full rounded-full bg-gradient-to-r from-admin-accent to-admin-warning"
                />
              </div>
              <span className="text-xs font-medium text-admin-text-bright tabular-nums w-28 text-right">{item.amount}</span>
              <span className="text-xs text-admin-text/60 w-10 text-right">{item.percentage}%</span>
            </div>
          ))}
        </div>
      </motion.div>
    </div>
  );
}
