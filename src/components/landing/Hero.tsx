import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { ArrowRight, Church, Users, Globe } from "lucide-react";
import elctIcon from "../../assets/elct-icon.png";
import rcIcon from "../../assets/rc-icon.png";
import sdaIcon from "../../assets/sda-icon.png";
import pentecostalIcon from "../../assets/pentecostal-icon.png";

const denominations = [
  { slug: "elct", name: "ELCT", icon: elctIcon, color: "border-blue-500/20 hover:border-blue-400/40" },
  { slug: "roman-catholic", name: "Roman Catholic", icon: rcIcon, color: "border-red-500/20 hover:border-red-400/40" },
  { slug: "sda", name: "SDA", icon: sdaIcon, color: "border-teal-500/20 hover:border-teal-400/40" },
  { slug: "pentecostal", name: "Pentecostal", icon: pentecostalIcon, color: "border-orange-500/20 hover:border-orange-400/40" },
];

const stats = [
  { value: "500+", label: "Churches", icon: Church },
  { value: "50K+", label: "Members", icon: Users },
  { value: "4", label: "Denominations", icon: Globe },
];

export default function Hero() {
  const scrollTo = (id: string) => {
    document.querySelector(id)?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section className="relative min-h-screen flex items-center bg-[hsl(220,30%,6%)] overflow-hidden">
      {/* Subtle grid */}
      <div
        className="absolute inset-0 opacity-[0.025]"
        style={{
          backgroundImage: `linear-gradient(hsl(0 0% 100%) 1px, transparent 1px), linear-gradient(90deg, hsl(0 0% 100%) 1px, transparent 1px)`,
          backgroundSize: "72px 72px",
        }}
      />

      {/* Ambient glow */}
      <div className="absolute -top-40 right-1/4 w-[500px] h-[500px] rounded-full bg-secondary/[0.06] blur-[160px]" />
      <div className="absolute bottom-0 -left-32 w-[400px] h-[400px] rounded-full bg-primary/[0.12] blur-[120px]" />

      <div className="max-w-7xl mx-auto px-6 w-full relative z-10 pt-32 pb-24">
        <div className="grid lg:grid-cols-[1fr,340px] gap-16 items-start">
          {/* Left column */}
          <div>
            {/* Badge */}
            <motion.div
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.4 }}
              className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/[0.06] bg-white/[0.02] mb-8"
            >
              <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
              <span className="text-xs font-medium text-white/40 tracking-wide">Available on iOS & Android</span>
            </motion.div>

            {/* Headline */}
            <motion.h1
              initial={{ opacity: 0, y: 28 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.1 }}
              className="text-[2.75rem] sm:text-6xl lg:text-7xl font-bold text-white font-display leading-[1.05] tracking-tight max-w-4xl"
            >
              All you need to manage
              <br />
              <span className="text-secondary">church operations</span>
            </motion.h1>

            {/* Subtitle */}
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.25 }}
              className="mt-6 text-base sm:text-lg text-white/35 leading-relaxed max-w-lg"
            >
              Finances, members, and communications for ELCT, Roman Catholic, SDA, and Pentecostal churches. All in one place.
            </motion.p>

            {/* CTAs */}
            <motion.div
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.4 }}
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
                className="px-7 py-3.5 text-white/50 border border-white/[0.08] rounded-xl font-medium text-sm hover:text-white hover:border-white/20 transition-all duration-300"
              >
                Learn More
              </button>
            </motion.div>

            {/* Denomination cards */}
            <motion.div
              initial={{ opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.55 }}
              className="mt-16 grid grid-cols-2 sm:grid-cols-4 gap-3"
            >
              {denominations.map((d, i) => (
                <motion.div
                  key={d.slug}
                  initial={{ opacity: 0, y: 16 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.6 + i * 0.08 }}
                >
                  <Link
                    to={`/churches/${d.slug}`}
                    className={`group flex items-center gap-3 p-4 rounded-2xl bg-white/[0.03] border ${d.color} transition-all duration-300 hover:bg-white/[0.05]`}
                  >
                    <img src={d.icon} alt={d.name} className="w-10 h-10 rounded-xl object-contain" />
                    <div className="flex-1 min-w-0">
                      <span className="text-sm font-bold text-white/80 block truncate">{d.name}</span>
                      <span className="text-[10px] text-white/25 uppercase tracking-wider">Explore</span>
                    </div>
                    <ArrowRight className="w-3.5 h-3.5 text-white/20 group-hover:text-white/50 group-hover:translate-x-0.5 transition-all shrink-0" />
                  </Link>
                </motion.div>
              ))}
            </motion.div>
          </div>

          {/* Right column: Stats */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.6, delay: 0.5 }}
            className="hidden lg:flex flex-col gap-5 mt-8"
          >
            {stats.map((stat, i) => (
              <motion.div
                key={stat.label}
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.6 + i * 0.12 }}
                className="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] backdrop-blur-sm"
              >
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 rounded-xl bg-secondary/10 flex items-center justify-center">
                    <stat.icon className="w-5 h-5 text-secondary" />
                  </div>
                  <div>
                    <div className="text-3xl font-bold text-white font-display">{stat.value}</div>
                    <div className="text-xs text-white/30 uppercase tracking-wider mt-0.5">{stat.label}</div>
                  </div>
                </div>
              </motion.div>
            ))}
          </motion.div>
        </div>

      </div>
    </section>
  );
}
