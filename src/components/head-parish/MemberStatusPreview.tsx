import { motion, AnimatePresence } from "framer-motion";
import { Target, TrendingUp, Wallet } from "lucide-react";

export interface StatusItem {
  label: string;
  value: string;
  color?: "primary" | "success" | "warning" | "danger" | "info";
  icon?: React.ReactNode;
}

interface MemberStatusPreviewProps {
  items: StatusItem[];
  visible: boolean;
}

const colorMap = {
  primary: "text-admin-accent",
  success: "text-emerald-400",
  warning: "text-amber-400",
  danger: "text-rose-400",
  info: "text-sky-400",
};

const bgMap = {
  primary: "bg-admin-accent/10",
  success: "bg-emerald-400/10",
  warning: "bg-amber-400/10",
  danger: "bg-rose-400/10",
  info: "bg-sky-400/10",
};

export default function MemberStatusPreview({ items, visible }: MemberStatusPreviewProps) {
  return (
    <AnimatePresence>
      {visible && items.length > 0 && (
        <motion.div
          initial={{ opacity: 0, height: 0, marginTop: 0 }}
          animate={{ opacity: 1, height: "auto", marginTop: 8 }}
          exit={{ opacity: 0, height: 0, marginTop: 0 }}
          transition={{ duration: 0.25 }}
          className="overflow-hidden"
        >
          <div className="rounded-xl border border-admin-accent/20 bg-admin-accent/5 p-4">
            <div className="flex flex-wrap gap-4">
              {items.map((item, i) => {
                const clr = item.color || "primary";
                return (
                  <div key={i} className={`flex items-center gap-2 px-3 py-2 rounded-lg ${bgMap[clr]}`}>
                    {item.icon && <span className={colorMap[clr]}>{item.icon}</span>}
                    <div>
                      <p className="text-[10px] uppercase tracking-wider text-admin-text/60 font-medium">{item.label}</p>
                      <p className={`text-sm font-bold ${colorMap[clr]}`}>{item.value}</p>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}

// Helper to generate mock harambee status
export function getHarambeeStatusItems(targetAmount: number, totalContribution: number): StatusItem[] {
  const balance = targetAmount - totalContribution;
  const isExtra = balance < 0;
  return [
    { label: "Member Target", value: `TZS ${targetAmount.toLocaleString()}`, color: "primary", icon: <Target size={16} /> },
    { label: "Total Contribution", value: `TZS ${totalContribution.toLocaleString()}`, color: "success", icon: <TrendingUp size={16} /> },
    { label: isExtra ? "Extra" : "Balance", value: `TZS ${Math.abs(balance).toLocaleString()}`, color: isExtra ? "warning" : "danger", icon: <Wallet size={16} /> },
  ];
}

// Helper to generate mock envelope status
export function getEnvelopeStatusItems(targetAmount: number, totalContribution: number): StatusItem[] {
  const balance = targetAmount - totalContribution;
  return [
    { label: "Envelope Target", value: `TZS ${targetAmount.toLocaleString()}`, color: "primary", icon: <Target size={16} /> },
    { label: "Total Paid", value: `TZS ${totalContribution.toLocaleString()}`, color: "success", icon: <TrendingUp size={16} /> },
    { label: "Remaining", value: `TZS ${Math.abs(balance).toLocaleString()}`, color: balance <= 0 ? "success" : "danger", icon: <Wallet size={16} /> },
  ];
}
