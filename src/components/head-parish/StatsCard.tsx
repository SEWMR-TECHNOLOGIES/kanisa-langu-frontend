import { motion } from "framer-motion";
import { TrendingUp, TrendingDown } from "lucide-react";

interface StatsCardProps {
  title: string;
  value: string | number;
  change?: string;
  trend?: "up" | "down" | "neutral";
  icon: React.ElementType;
  color?: "gold" | "info" | "success" | "warning";
  delay?: number;
}

const colorMap = {
  gold: { bg: "from-admin-accent/20 to-admin-accent/5", icon: "text-admin-accent", glow: "shadow-[0_0_30px_-8px_hsla(42,92%,56%,0.2)]" },
  info: { bg: "from-admin-info/20 to-admin-info/5", icon: "text-admin-info", glow: "shadow-[0_0_30px_-8px_hsla(210,80%,56%,0.2)]" },
  success: { bg: "from-admin-success/20 to-admin-success/5", icon: "text-admin-success", glow: "shadow-[0_0_30px_-8px_hsla(152,69%,48%,0.2)]" },
  warning: { bg: "from-admin-warning/20 to-admin-warning/5", icon: "text-admin-warning", glow: "shadow-[0_0_30px_-8px_hsla(38,92%,50%,0.2)]" },
};

export default function StatsCard({ title, value, change, trend = "neutral", icon: Icon, color = "gold", delay = 0 }: StatsCardProps) {
  const c = colorMap[color];

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay }}
      className={`admin-card rounded-2xl p-5 lg:p-6 ${c.glow} hover:scale-[1.02] transition-transform duration-200`}
    >
      <div className="flex items-start justify-between">
        <div className="space-y-3">
          <p className="text-xs font-medium uppercase tracking-wider text-admin-text/60">{title}</p>
          <p className="text-2xl lg:text-3xl font-bold text-admin-text-bright font-display tabular-nums">{value}</p>
          {change && (
            <div className="flex items-center gap-1.5">
              {trend === "up" && <TrendingUp className="w-3.5 h-3.5 text-admin-success" />}
              {trend === "down" && <TrendingDown className="w-3.5 h-3.5 text-destructive" />}
              <span className={`text-xs font-medium ${trend === "up" ? "text-admin-success" : trend === "down" ? "text-destructive" : "text-admin-text"}`}>
                {change}
              </span>
            </div>
          )}
        </div>
        <div className={`w-12 h-12 rounded-2xl bg-gradient-to-br ${c.bg} flex items-center justify-center`}>
          <Icon className={`w-6 h-6 ${c.icon}`} />
        </div>
      </div>
    </motion.div>
  );
}
