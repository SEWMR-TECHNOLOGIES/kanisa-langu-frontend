import { motion } from "framer-motion";
import heroPattern from "../../assets/hero-pattern.png";

export default function Hero() {
  const scrollTo = (id: string) => {
    document.querySelector(id)?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section className="relative min-h-screen flex items-center overflow-hidden">
      {/* Dark background with pattern */}
      <div className="absolute inset-0">
        <img src={heroPattern} alt="" className="absolute inset-0 w-full h-full object-cover" />
        <div className="absolute inset-0 bg-gradient-to-b from-black/40 via-transparent to-black/60" />
      </div>

      {/* Glowing orbs */}
      <div className="absolute top-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-secondary/8 blur-[120px]" />
      <div className="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] rounded-full bg-primary/10 blur-[100px]" />

      <div className="max-w-7xl mx-auto px-6 w-full relative z-10 pt-20">
        <div className="max-w-3xl">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, delay: 0.1 }}
          >
            <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 backdrop-blur-sm mb-8">
              <span className="w-2 h-2 rounded-full bg-secondary animate-pulse" />
              <span className="text-xs font-semibold text-white/70 uppercase tracking-widest">
                Trusted by 500+ churches across Tanzania
              </span>
            </div>
          </motion.div>

          <motion.h1
            initial={{ opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-bold text-white font-display leading-[0.95] tracking-tight"
          >
            The future of
            <br />
            <span className="text-gradient-gold">church</span> management
          </motion.h1>

          <motion.p
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, delay: 0.4 }}
            className="mt-7 text-lg sm:text-xl text-white/50 leading-relaxed max-w-xl"
          >
            One platform for ELCT, Roman Catholic, SDA, and Pentecostal churches. 
            Manage finances, members, and communications — all in one place.
          </motion.p>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="flex flex-wrap gap-4 mt-10"
          >
            <button
              onClick={() => scrollTo("#churches")}
              className="group px-8 py-4 bg-secondary text-secondary-foreground rounded-2xl font-bold text-sm shadow-xl shadow-secondary/25 hover:shadow-2xl hover:shadow-secondary/30 hover:-translate-y-0.5 transition-all duration-300"
            >
              Explore Your Church →
            </button>
            <button
              onClick={() => scrollTo("#features")}
              className="px-8 py-4 bg-white/5 text-white border border-white/10 rounded-2xl font-bold text-sm hover:bg-white/10 hover:-translate-y-0.5 transition-all duration-300 backdrop-blur-sm"
            >
              See Features
            </button>
          </motion.div>

          {/* Stats row */}
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.8, delay: 0.9 }}
            className="flex gap-12 mt-16 pt-10 border-t border-white/10"
          >
            {[
              { value: "500+", label: "Churches" },
              { value: "50K+", label: "Members" },
              { value: "4", label: "Denominations" },
            ].map((stat) => (
              <div key={stat.label}>
                <div className="text-3xl sm:text-4xl font-bold text-white font-display">{stat.value}</div>
                <div className="text-sm text-white/40 mt-1">{stat.label}</div>
              </div>
            ))}
          </motion.div>
        </div>
      </div>
    </section>
  );
}