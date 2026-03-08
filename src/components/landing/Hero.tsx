import { motion } from "framer-motion";
import logo from "../../assets/logo.png";

export default function Hero() {
  const scrollTo = (id: string) => {
    document.querySelector(id)?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section className="relative min-h-screen flex items-center bg-[hsl(220,30%,6%)] overflow-hidden">
      {/* Subtle grid */}
      <div
        className="absolute inset-0 opacity-[0.03]"
        style={{
          backgroundImage: `linear-gradient(hsl(0 0% 100%) 1px, transparent 1px), linear-gradient(90deg, hsl(0 0% 100%) 1px, transparent 1px)`,
          backgroundSize: "80px 80px",
        }}
      />

      {/* Ambient glow - top right */}
      <div className="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full bg-secondary/[0.07] blur-[150px]" />
      {/* Ambient glow - bottom left */}
      <div className="absolute -bottom-48 -left-48 w-[500px] h-[500px] rounded-full bg-primary/[0.15] blur-[130px]" />

      <div className="max-w-7xl mx-auto px-6 w-full relative z-10 pt-32 pb-20">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Left content */}
          <div>
            <motion.div
              initial={{ opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5 }}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/[0.08] bg-white/[0.03] mb-8"
            >
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
              <span className="text-xs font-medium text-white/50 tracking-wide">Now available on iOS & Android</span>
            </motion.div>

            <motion.h1
              initial={{ opacity: 0, y: 32 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.15 }}
              className="text-[2.75rem] sm:text-6xl lg:text-[4.25rem] font-bold text-white font-display leading-[1.05] tracking-tight"
            >
              Manage your
              <br />
              church with
              <br />
              <span className="text-secondary">clarity</span>
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.3 }}
              className="mt-6 text-base sm:text-lg text-white/40 leading-relaxed max-w-md"
            >
              One platform for ELCT, Roman Catholic, SDA, and Pentecostal churches. Finances, members, and communications in one place.
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.45 }}
              className="flex flex-wrap gap-3 mt-8"
            >
              <button
                onClick={() => scrollTo("#churches")}
                className="px-7 py-3.5 bg-secondary text-secondary-foreground rounded-xl font-bold text-sm hover:-translate-y-0.5 transition-all duration-300"
              >
                Get Started
              </button>
              <button
                onClick={() => scrollTo("#features")}
                className="px-7 py-3.5 text-white/60 border border-white/[0.08] rounded-xl font-medium text-sm hover:text-white hover:border-white/20 transition-all duration-300"
              >
                Learn More
              </button>
            </motion.div>

            {/* Minimal stats */}
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.6, delay: 0.7 }}
              className="flex gap-10 mt-14"
            >
              {[
                { value: "500+", label: "Churches" },
                { value: "50K+", label: "Members" },
                { value: "4", label: "Denominations" },
              ].map((stat) => (
                <div key={stat.label}>
                  <div className="text-2xl font-bold text-white font-display">{stat.value}</div>
                  <div className="text-xs text-white/25 mt-0.5 uppercase tracking-wider">{stat.label}</div>
                </div>
              ))}
            </motion.div>
          </div>

          {/* Right side - abstract visual */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.8, delay: 0.3 }}
            className="hidden lg:flex items-center justify-center"
          >
            <div className="relative w-full max-w-md aspect-square">
              {/* Concentric rings */}
              <div className="absolute inset-0 rounded-full border border-white/[0.04]" />
              <div className="absolute inset-8 rounded-full border border-white/[0.06]" />
              <div className="absolute inset-16 rounded-full border border-white/[0.08]" />
              <div className="absolute inset-24 rounded-full border border-white/[0.10]" />

              {/* Center logo */}
              <div className="absolute inset-0 flex items-center justify-center">
                <div className="w-24 h-24 rounded-3xl bg-white/[0.05] border border-white/[0.08] flex items-center justify-center backdrop-blur-sm">
                  <img src={logo} alt="Kanisa Langu" className="w-14 h-14" />
                </div>
              </div>

              {/* Floating denomination indicators */}
              {[
                { label: "ELCT", angle: -45, color: "bg-blue-400" },
                { label: "RC", angle: 45, color: "bg-red-400" },
                { label: "SDA", angle: 135, color: "bg-teal-400" },
                { label: "PENT", angle: 225, color: "bg-orange-400" },
              ].map((item, i) => {
                const radius = 160;
                const rad = (item.angle * Math.PI) / 180;
                const x = Math.cos(rad) * radius;
                const y = Math.sin(rad) * radius;
                return (
                  <motion.div
                    key={item.label}
                    initial={{ opacity: 0, scale: 0 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ delay: 0.8 + i * 0.1, duration: 0.4 }}
                    className="absolute left-1/2 top-1/2"
                    style={{ transform: `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))` }}
                  >
                    <div className="px-3 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.08] backdrop-blur-sm flex items-center gap-2">
                      <span className={`w-1.5 h-1.5 rounded-full ${item.color}`} />
                      <span className="text-[10px] font-bold text-white/50 tracking-wider">{item.label}</span>
                    </div>
                  </motion.div>
                );
              })}
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
