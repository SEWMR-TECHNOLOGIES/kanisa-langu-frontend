import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import { Building, Eye, EyeOff, Lock, Mail, ArrowRight } from "lucide-react";

export default function SignIn() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    // Mock login - navigate to dashboard
    setTimeout(() => {
      setIsLoading(false);
      navigate("/elct/head-parish");
    }, 1500);
  };

  return (
    <div className="min-h-screen flex relative overflow-hidden" style={{ background: "hsl(220, 25%, 4%)" }}>
      {/* Ambient background effects */}
      <div className="absolute inset-0">
        <div className="absolute top-0 left-1/4 w-[600px] h-[600px] rounded-full opacity-[0.07]"
          style={{ background: "radial-gradient(circle, hsl(42, 92%, 56%) 0%, transparent 70%)" }} />
        <div className="absolute bottom-0 right-1/4 w-[800px] h-[800px] rounded-full opacity-[0.04]"
          style={{ background: "radial-gradient(circle, hsl(210, 80%, 56%) 0%, transparent 70%)" }} />
        {/* Grid pattern */}
        <div className="absolute inset-0 opacity-[0.03]"
          style={{ backgroundImage: "radial-gradient(hsl(220, 10%, 50%) 1px, transparent 1px)", backgroundSize: "32px 32px" }} />
      </div>

      {/* Left panel - Branding */}
      <div className="hidden lg:flex lg:w-1/2 relative z-10 flex-col justify-between p-12">
        <div>
          <div className="flex items-center gap-3">
            <div className="w-11 h-11 rounded-2xl bg-gradient-to-br from-gold to-amber-600 flex items-center justify-center">
              <Building className="w-6 h-6" style={{ color: "hsl(220, 25%, 4%)" }} />
            </div>
            <div>
              <h2 className="text-lg font-bold tracking-tight" style={{ color: "hsl(220, 10%, 92%)" }}>Kanisa Langu</h2>
              <p className="text-[10px] uppercase tracking-[0.2em]" style={{ color: "hsl(220, 10%, 50%)" }}>Church Management System</p>
            </div>
          </div>
        </div>

        <div className="space-y-8">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
          >
            <h1 className="text-5xl lg:text-6xl font-bold leading-[1.1] font-display" style={{ color: "hsl(220, 10%, 92%)" }}>
              Head Parish
              <br />
              <span className="text-gradient-gold">Admin Portal</span>
            </h1>
            <p className="text-lg mt-6 max-w-md leading-relaxed" style={{ color: "hsl(220, 10%, 50%)" }}>
              Manage your parish, track contributions, oversee finances, and lead your church community with confidence.
            </p>
          </motion.div>

          {/* Feature highlights */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="grid grid-cols-2 gap-4 max-w-md"
          >
            {[
              { label: "Members", value: "2,847" },
              { label: "Revenue Tracked", value: "TZS 15.2M" },
              { label: "Sub Parishes", value: "8" },
              { label: "Communities", value: "15" },
            ].map((stat, i) => (
              <div key={stat.label} className="p-4 rounded-2xl" style={{ background: "hsla(220, 25%, 8%, 0.8)", border: "1px solid hsla(220, 20%, 14%, 0.5)" }}>
                <p className="text-2xl font-bold font-display" style={{ color: "hsl(42, 92%, 56%)" }}>{stat.value}</p>
                <p className="text-xs mt-1" style={{ color: "hsl(220, 10%, 50%)" }}>{stat.label}</p>
              </div>
            ))}
          </motion.div>
        </div>

        <div>
          <p className="text-xs" style={{ color: "hsl(220, 10%, 30%)" }}>
            ELCT Church Management &middot; Evangelical Lutheran Church in Tanzania
          </p>
        </div>
      </div>

      {/* Right panel - Login form */}
      <div className="flex-1 flex items-center justify-center relative z-10 p-6 lg:p-12">
        <motion.div
          initial={{ opacity: 0, scale: 0.96 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.5, delay: 0.3 }}
          className="w-full max-w-[420px]"
        >
          {/* Mobile brand */}
          <div className="lg:hidden flex items-center gap-3 mb-10 justify-center">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-br from-gold to-amber-600 flex items-center justify-center">
              <Building className="w-5 h-5" style={{ color: "hsl(220, 25%, 4%)" }} />
            </div>
            <div>
              <h2 className="text-base font-bold" style={{ color: "hsl(220, 10%, 92%)" }}>Kanisa Langu</h2>
              <p className="text-[9px] uppercase tracking-[0.2em]" style={{ color: "hsl(220, 10%, 50%)" }}>Head Parish Admin</p>
            </div>
          </div>

          {/* Card */}
          <div className="rounded-3xl p-8 lg:p-10" style={{
            background: "hsla(220, 25%, 7%, 0.9)",
            border: "1px solid hsla(220, 20%, 14%, 0.6)",
            backdropFilter: "blur(20px)",
            boxShadow: "0 25px 50px -12px hsla(0, 0%, 0%, 0.5)"
          }}>
            <div className="mb-8">
              <h3 className="text-2xl font-bold font-display" style={{ color: "hsl(220, 10%, 92%)" }}>
                Welcome back
              </h3>
              <p className="text-sm mt-2" style={{ color: "hsl(220, 10%, 50%)" }}>
                Sign in to your head parish admin account
              </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-5">
              {/* Email */}
              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider mb-2.5" style={{ color: "hsl(220, 10%, 50%)" }}>
                  Email Address
                </label>
                <div className="relative">
                  <Mail className="absolute left-4 top-1/2 -translate-y-1/2 w-[18px] h-[18px]" style={{ color: "hsl(220, 10%, 35%)" }} />
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="admin@kanisalangu.com"
                    required
                    className="w-full pl-12 pr-4 py-3.5 rounded-xl text-sm outline-none transition-all"
                    style={{
                      background: "hsla(220, 25%, 5%, 0.8)",
                      border: "1px solid hsla(220, 20%, 14%, 0.5)",
                      color: "hsl(220, 10%, 92%)",
                    }}
                  />
                </div>
              </div>

              {/* Password */}
              <div>
                <label className="block text-[11px] font-semibold uppercase tracking-wider mb-2.5" style={{ color: "hsl(220, 10%, 50%)" }}>
                  Password
                </label>
                <div className="relative">
                  <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-[18px] h-[18px]" style={{ color: "hsl(220, 10%, 35%)" }} />
                  <input
                    type={showPassword ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter your password"
                    required
                    className="w-full pl-12 pr-12 py-3.5 rounded-xl text-sm outline-none transition-all"
                    style={{
                      background: "hsla(220, 25%, 5%, 0.8)",
                      border: "1px solid hsla(220, 20%, 14%, 0.5)",
                      color: "hsl(220, 10%, 92%)",
                    }}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 p-0.5"
                    style={{ color: "hsl(220, 10%, 35%)" }}
                  >
                    {showPassword ? <EyeOff className="w-[18px] h-[18px]" /> : <Eye className="w-[18px] h-[18px]" />}
                  </button>
                </div>
              </div>

              {/* Remember / Forgot */}
              <div className="flex items-center justify-between">
                <label className="flex items-center gap-2 cursor-pointer">
                  <div className="w-4 h-4 rounded border flex items-center justify-center" style={{ borderColor: "hsl(220, 20%, 20%)" }}>
                    <div className="w-2 h-2 rounded-sm bg-gold" />
                  </div>
                  <span className="text-xs" style={{ color: "hsl(220, 10%, 50%)" }}>Remember me</span>
                </label>
                <button type="button" className="text-xs font-medium text-gold hover:underline">
                  Forgot password?
                </button>
              </div>

              {/* Submit */}
              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-3.5 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all hover:opacity-90 disabled:opacity-60"
                style={{
                  background: "linear-gradient(135deg, hsl(42, 92%, 56%), hsl(32, 90%, 48%))",
                  color: "hsl(220, 25%, 4%)",
                  boxShadow: "0 0 30px -8px hsla(42, 92%, 56%, 0.3)",
                }}
              >
                {isLoading ? (
                  <motion.div
                    animate={{ rotate: 360 }}
                    transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                    className="w-5 h-5 border-2 border-current border-t-transparent rounded-full"
                  />
                ) : (
                  <>
                    Sign In
                    <ArrowRight className="w-4 h-4" />
                  </>
                )}
              </button>
            </form>

            {/* Divider */}
            <div className="flex items-center gap-4 my-6">
              <div className="flex-1 h-px" style={{ background: "hsl(220, 20%, 14%)" }} />
              <span className="text-[10px] uppercase tracking-wider" style={{ color: "hsl(220, 10%, 30%)" }}>or</span>
              <div className="flex-1 h-px" style={{ background: "hsl(220, 20%, 14%)" }} />
            </div>

            {/* Contact admin */}
            <p className="text-center text-xs" style={{ color: "hsl(220, 10%, 40%)" }}>
              Don't have an account?{" "}
              <span className="text-gold font-medium">Contact your Diocese Admin</span>
            </p>
          </div>

          {/* Footer */}
          <p className="text-center text-[10px] mt-6" style={{ color: "hsl(220, 10%, 25%)" }}>
            Powered by Kanisa Langu &middot; Secure & Encrypted
          </p>
        </motion.div>
      </div>
    </div>
  );
}
