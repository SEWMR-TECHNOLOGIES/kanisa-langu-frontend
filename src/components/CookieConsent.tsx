import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { motion, AnimatePresence } from "framer-motion";
import { X, Cookie } from "lucide-react";

export default function CookieConsent() {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const consent = localStorage.getItem("kl-cookie-consent");
    if (!consent) {
      const timer = setTimeout(() => setVisible(true), 1500);
      return () => clearTimeout(timer);
    }
  }, []);

  const accept = () => {
    localStorage.setItem("kl-cookie-consent", "accepted");
    setVisible(false);
  };

  const decline = () => {
    localStorage.setItem("kl-cookie-consent", "declined");
    setVisible(false);
  };

  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          initial={{ y: 100, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          exit={{ y: 100, opacity: 0 }}
          transition={{ type: "spring", damping: 25, stiffness: 300 }}
          className="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6"
        >
          <div className="max-w-lg mx-auto sm:ml-auto sm:mr-6 p-5 rounded-2xl bg-card border border-border shadow-2xl shadow-black/10">
            <div className="flex items-start justify-between gap-3 mb-3">
              <div className="flex items-center gap-2.5">
                <Cookie className="w-4 h-4 text-secondary shrink-0" />
                <h3 className="text-sm font-bold text-foreground">We use cookies</h3>
              </div>
              <button onClick={decline} className="text-muted-foreground hover:text-foreground transition-colors">
                <X className="w-4 h-4" />
              </button>
            </div>
            <p className="text-xs text-muted-foreground leading-relaxed mb-4">
              We use cookies to improve your experience, analyze usage, and keep you signed in. Read our{" "}
              <Link to="/cookies" className="text-secondary hover:underline">Cookie Policy</Link>{" "}
              for details.
            </p>
            <div className="flex gap-2">
              <button
                onClick={accept}
                className="flex-1 px-4 py-2.5 bg-secondary text-secondary-foreground rounded-xl text-xs font-bold hover:opacity-90 transition-opacity"
              >
                Accept All
              </button>
              <button
                onClick={decline}
                className="flex-1 px-4 py-2.5 bg-muted text-muted-foreground rounded-xl text-xs font-bold hover:bg-muted/80 transition-colors"
              >
                Essential Only
              </button>
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
