import { useLocation } from "react-router-dom";
import { motion } from "framer-motion";
import { Construction } from "lucide-react";

export default function SubParishGenericPage() {
  const location = useLocation();
  const pageName = location.pathname.split("/").pop()?.replace(/-/g, " ").replace(/\b\w/g, c => c.toUpperCase()) || "Page";

  return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] text-center">
      <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="space-y-4">
        <div className="w-16 h-16 rounded-2xl bg-admin-surface-hover flex items-center justify-center mx-auto">
          <Construction className="w-8 h-8 text-admin-accent" />
        </div>
        <h1 className="text-2xl font-bold text-admin-text-bright font-display">{pageName}</h1>
        <p className="text-sm text-admin-text max-w-md">This page is being built. Full functionality coming soon.</p>
      </motion.div>
    </div>
  );
}
