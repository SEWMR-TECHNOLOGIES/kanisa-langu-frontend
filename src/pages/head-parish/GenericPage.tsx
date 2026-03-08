import { motion } from "framer-motion";
import { Construction } from "lucide-react";
import { useLocation } from "react-router-dom";

export default function GenericPage() {
  const location = useLocation();
  const pageName = location.pathname.split("/").pop()?.replace(/-/g, " ").replace(/\b\w/g, c => c.toUpperCase()) || "Page";
  
  return (
    <div className="flex items-center justify-center min-h-[60vh]">
      <motion.div
        initial={{ opacity: 0, scale: 0.95 }}
        animate={{ opacity: 1, scale: 1 }}
        className="admin-card rounded-2xl p-12 text-center max-w-md"
      >
        <div className="w-16 h-16 rounded-2xl bg-admin-accent/10 flex items-center justify-center mx-auto mb-6">
          <Construction className="w-8 h-8 text-admin-accent" />
        </div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display mb-3">{pageName}</h1>
        <p className="text-sm text-admin-text">This page is being built. The functionality from the legacy system will be available here soon.</p>
      </motion.div>
    </div>
  );
}
