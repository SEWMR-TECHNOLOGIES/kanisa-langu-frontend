import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { motion } from "framer-motion";
import { Eye, EyeOff, Lock, Mail, ArrowRight, ArrowLeft } from "lucide-react";
import type { LucideIcon } from "lucide-react";
import logo from "../../assets/kanisa-logo.png";

interface ElctSignInProps {
  level: string;
  icon: LucideIcon;
  accentFrom: string;
  accentTo: string;
  dashboardPath: string;
  contactLabel: string;
  stats: { label: string; value: string }[];
  description: string;
}

export default function ElctSignIn({
  level,
  accentFrom,
  accentTo,
  dashboardPath,
  contactLabel,
  stats,
  description,
}: ElctSignInProps) {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setTimeout(() => {
      setIsLoading(false);
      navigate(dashboardPath);
    }, 1500);
  };

  const gradient = `linear-gradient(135deg, ${accentFrom}, ${accentTo})`;

  return (
    <div className="min-h-screen flex relative overflow-hidden bg-[hsl(220,20%,97%)]">
      {/* Ambient background */}
      <div className="absolute inset-0">
        <div className="absolute top-0 left-1/4 w-[600px] h-[600px] rounded-full opacity-[0.08]"
          style={{ background: `radial-gradient(circle, ${accentFrom} 0%, transparent 70%)` }} />
        <div className="absolute bottom-0 right-1/4 w-[800px] h-[800px] rounded-full opacity-[0.04]"
          style={{ background: "radial-gradient(circle, hsl(210, 80%, 56%) 0%, transparent 70%)" }} />
        <div className="absolute inset-0 opacity-[0.03]"
          style={{ backgroundImage: "radial-gradient(hsl(220, 10%, 70%) 1px, transparent 1px)", backgroundSize: "32px 32px" }} />
      </div>

      {/* Left panel */}
      <div className="hidden lg:flex lg:w-1/2 relative z-10 flex-col justify-between p-12"
        style={{ background: "linear-gradient(135deg, hsl(220, 30%, 8%) 0%, hsl(220, 25%, 4%) 100%)" }}>
        <div>
          <Link to="/churches/elct" className="inline-flex items-center gap-2 text-sm font-medium mb-8 transition-colors text-white/40 hover:text-white/70">
            <ArrowLeft className="w-4 h-4" /> Back to ELCT
          </Link>
          <div className="flex items-center gap-3">
            <img src={logo} alt="Kanisa Langu" className="w-11 h-11 rounded-2xl" />
            <div>
              <h2 className="text-lg font-bold tracking-tight text-white">Kanisa Langu</h2>
              <p className="text-[10px] uppercase tracking-[0.2em] text-white/50">Church Management System</p>
            </div>
          </div>
        </div>

        <div className="space-y-8">
          <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8, delay: 0.2 }}>
            <h1 className="text-5xl lg:text-6xl font-bold leading-[1.1] font-display text-white">
              {level}
              <br />
              <span style={{ background: gradient, WebkitBackgroundClip: "text", WebkitTextFillColor: "transparent" }}>
                Admin Portal
              </span>
            </h1>
            <p className="text-lg mt-6 max-w-md leading-relaxed text-white/50">
              {description}
            </p>
          </motion.div>

          <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6, delay: 0.6 }}
            className="grid grid-cols-2 gap-4 max-w-md">
            {stats.map((stat) => (
              <div key={stat.label} className="p-4 rounded-2xl bg-white/[0.05] border border-white/[0.08]">
                <p className="text-2xl font-bold font-display" style={{ color: accentFrom }}>{stat.value}</p>
                <p className="text-xs mt-1 text-white/50">{stat.label}</p>
              </div>
            ))}
          </motion.div>
        </div>

        <p className="text-xs text-white/30">
          ELCT Church Management &middot; Evangelical Lutheran Church in Tanzania
        </p>
      </div>

      {/* Right panel - Login form */}
      <div className="flex-1 flex items-center justify-center relative z-10 p-6 lg:p-12">
        <motion.div initial={{ opacity: 0, scale: 0.96 }} animate={{ opacity: 1, scale: 1 }} transition={{ duration: 0.5, delay: 0.3 }}
          className="w-full max-w-[420px]">
          
          {/* Mobile brand */}
          <div className="lg:hidden mb-10">
            <Link to="/churches/elct" className="inline-flex items-center gap-2 text-xs font-medium mb-6 text-muted-foreground">
              <ArrowLeft className="w-3.5 h-3.5" /> Back to ELCT
            </Link>
            <div className="flex items-center gap-3 justify-center">
              <img src={logo} alt="Kanisa Langu" className="w-10 h-10 rounded-2xl" />
              <div>
                <h2 className="text-base font-bold text-foreground">Kanisa Langu</h2>
                <p className="text-[9px] uppercase tracking-[0.2em] text-muted-foreground">{level} Admin</p>
              </div>
            </div>
          </div>

          {/* Card */}
          <div className="rounded-3xl p-8 lg:p-10 bg-white border border-border shadow-xl shadow-black/[0.03]">
            {/* Logo centered */}
            <div className="flex justify-center mb-6">
              <img src={logo} alt="Kanisa Langu" className="w-16 h-16 rounded-2xl" />
            </div>
            <div className="mb-8 text-center">
              <h3 className="text-2xl font-bold font-display text-foreground">Welcome back</h3>
              <p className="text-sm mt-2 text-muted-foreground">
                Sign in to your {level.toLowerCase()} admin account
              </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider mb-2.5 text-muted-foreground">
                  Email Address
                </label>
                <div className="relative">
                  <Mail className="absolute left-4 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-muted-foreground/60" />
                  <input type="email" value={email} onChange={(e) => setEmail(e.target.value)}
                    placeholder="admin@kanisalangu.com" required
                    className="w-full pl-12 pr-4 py-3.5 rounded-xl text-sm outline-none transition-all bg-muted/50 border border-border text-foreground placeholder:text-muted-foreground/50 focus:border-[hsl(42,92%,50%)] focus:ring-2 focus:ring-[hsla(42,92%,50%,0.15)]" />
                </div>
              </div>

              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider mb-2.5 text-muted-foreground">
                  Password
                </label>
                <div className="relative">
                  <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-muted-foreground/60" />
                  <input type={showPassword ? "text" : "password"} value={password} onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter your password" required
                    className="w-full pl-12 pr-12 py-3.5 rounded-xl text-sm outline-none transition-all bg-muted/50 border border-border text-foreground placeholder:text-muted-foreground/50 focus:border-[hsl(42,92%,50%)] focus:ring-2 focus:ring-[hsla(42,92%,50%,0.15)]" />
                  <button type="button" onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 p-0.5 text-muted-foreground/60 hover:text-foreground">
                    {showPassword ? <EyeOff className="w-[18px] h-[18px]" /> : <Eye className="w-[18px] h-[18px]" />}
                  </button>
                </div>
              </div>

              <div className="flex items-center justify-between">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" className="w-4 h-4 rounded border-border accent-[hsl(42,92%,50%)]" />
                  <span className="text-xs text-muted-foreground">Remember me</span>
                </label>
                <button type="button" className="text-xs font-medium hover:underline" style={{ color: accentFrom }}>
                  Forgot password?
                </button>
              </div>

              <button type="submit" disabled={isLoading}
                className="w-full py-3.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all hover:opacity-90 disabled:opacity-60 text-white"
                style={{ background: gradient, boxShadow: `0 4px 14px -4px ${accentFrom}60` }}>
                {isLoading ? (
                  <motion.div animate={{ rotate: 360 }} transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                    className="w-5 h-5 border-2 border-white border-t-transparent rounded-full" />
                ) : (
                  <>Sign In <ArrowRight className="w-4 h-4" /></>
                )}
              </button>
            </form>

            <div className="flex items-center gap-4 my-6">
              <div className="flex-1 h-px bg-border" />
              <span className="text-[10px] uppercase tracking-wider text-muted-foreground/50">or</span>
              <div className="flex-1 h-px bg-border" />
            </div>

            <p className="text-center text-xs text-muted-foreground">
              Don't have an account?{" "}
              <span className="font-medium" style={{ color: accentFrom }}>{contactLabel}</span>
            </p>
          </div>

          <p className="text-center text-[10px] mt-6 text-muted-foreground/50">
            Powered by Kanisa Langu &middot; Secure & Encrypted
          </p>
        </motion.div>
      </div>
    </div>
  );
}
