import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { motion } from "framer-motion";
import { Eye, EyeOff, Lock, User, ArrowRight, Shield } from "lucide-react";
import logo from "../../assets/kanisa-logo.png";

export default function SuperAdminSignIn() {
  const navigate = useNavigate();
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      navigate("/app");
    }, 1200);
  };

  return (
    <div className="min-h-screen flex relative overflow-hidden bg-[hsl(220,20%,97%)]">
      {/* Ambient background */}
      <div className="absolute inset-0">
        <div className="absolute top-0 right-0 w-[600px] h-[600px] rounded-full bg-[hsl(42,92%,50%)]/5 blur-3xl" />
        <div className="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full bg-[hsl(220,72%,20%)]/5 blur-3xl" />
      </div>

      <div className="flex-1 flex items-center justify-center p-6 relative z-10">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="w-full max-w-md"
        >
          {/* Logo */}
          <div className="text-center mb-8">
            <Link to="/">
              <img src={logo} alt="Kanisa Langu" className="h-14 w-14 mx-auto mb-4 object-contain" />
            </Link>
            <h1 className="text-2xl font-bold text-[hsl(220,30%,12%)] font-display">Super Admin Login</h1>
            <p className="text-sm text-[hsl(220,10%,46%)] mt-1">Connect, Worship, Engage</p>
          </div>

          {/* Card */}
          <div className="bg-white rounded-2xl border border-[hsl(220,14%,90%)] p-8 shadow-sm">
            <div className="flex items-center gap-2 mb-6 justify-center">
              <Shield className="w-5 h-5 text-[hsl(42,92%,50%)]" />
              <span className="text-xs font-bold uppercase tracking-wider text-[hsl(220,10%,46%)]">Secure Access</span>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
              <div>
                <label className="block text-xs font-medium text-[hsl(220,10%,46%)] mb-2 uppercase tracking-wider">Username</label>
                <div className="relative">
                  <User className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[hsl(220,10%,46%)]/50" />
                  <input
                    type="text"
                    placeholder="Enter your username"
                    className="admin-input w-full rounded-xl pl-11 pr-4 py-3 text-sm outline-none transition-all"
                    required
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-[hsl(220,10%,46%)] mb-2 uppercase tracking-wider">Password</label>
                <div className="relative">
                  <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[hsl(220,10%,46%)]/50" />
                  <input
                    type={showPassword ? "text" : "password"}
                    placeholder="Enter your password"
                    className="admin-input w-full rounded-xl pl-11 pr-12 py-3 text-sm outline-none transition-all"
                    required
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 text-[hsl(220,10%,46%)]/50 hover:text-[hsl(220,10%,46%)] transition-colors"
                  >
                    {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              <div className="flex items-center justify-between">
                <label className="modern-checkbox flex items-center gap-2.5 cursor-pointer select-none group">
                  <input type="checkbox" className="sr-only" />
                  <div className="cb-box w-[18px] h-[18px] rounded-md border-2 border-[hsl(220,14%,90%)] transition-all duration-200 flex items-center justify-center group-hover:border-[hsl(42,92%,50%)]/60">
                    <svg className="cb-check w-3 h-3 text-white opacity-0 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </div>
                  <span className="text-xs text-[hsl(220,10%,46%)]">Remember me</span>
                </label>
                <button type="button" className="text-xs font-medium text-[hsl(42,92%,50%)] hover:underline">
                  Forgot password?
                </button>
              </div>

              <button
                type="submit"
                disabled={loading}
                className="w-full py-3.5 rounded-xl bg-gradient-to-r from-[hsl(42,92%,50%)] to-[hsl(32,90%,48%)] text-white font-semibold text-sm hover:opacity-90 transition-all flex items-center justify-center gap-2 disabled:opacity-60"
              >
                {loading ? (
                  <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                ) : (
                  <>
                    Sign In
                    <ArrowRight className="w-4 h-4" />
                  </>
                )}
              </button>
            </form>
          </div>
        </motion.div>
      </div>
    </div>
  );
}
