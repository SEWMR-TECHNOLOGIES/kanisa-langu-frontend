import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { ArrowRight, Church, Users, Globe } from "lucide-react";
import elctIcon from "../../assets/elct-icon.png";
import rcIcon from "../../assets/rc-icon.png";
import sdaIcon from "../../assets/sda-icon.png";
import pentecostalIcon from "../../assets/pentecostal-icon.png";

const denominations = [
  { slug: "elct", name: "ELCT", icon: elctIcon, color: "border-blue-500/20 hover:border-blue-500/40 bg-blue-500/[0.04]" },
  { slug: "roman-catholic", name: "Roman Catholic", icon: rcIcon, color: "border-red-500/20 hover:border-red-500/40 bg-red-500/[0.04]" },
  { slug: "sda", name: "SDA", icon: sdaIcon, color: "border-teal-500/20 hover:border-teal-500/40 bg-teal-500/[0.04]" },
  { slug: "pentecostal", name: "Pentecostal", icon: pentecostalIcon, color: "border-orange-500/20 hover:border-orange-500/40 bg-orange-500/[0.04]" },
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
    <section className="relative min-h-screen flex items-center bg-background overflow-hidden">
      {/* Subtle dot grid */}
      <div
        className="absolute inset-0 opacity-[0.4]"
        style={{
          backgroundImage: `radial-gradient(circle, hsl(var(--border)) 1px, transparent 1px)`,
          backgroundSize: "32px 32px",
        }}
      />

      {/* Ambient glows */}
      <div className="absolute -top-40 right-1/4 w-[600px] h-[600px] rounded-full bg-secondary/[0.08] blur-[200px]" />
      <div className="absolute bottom-0 -left-32 w-[400px] h-[400px] rounded-full bg-primary/[0.06] blur-[160px]" />

      <div className="max-w-7xl mx-auto px-6 w-full relative z-10 pt-32 pb-20">
        <div className="grid lg:grid-cols-[1fr,380px] gap-12 lg:gap-20 items-center">
          {/* Left column */}
          <div>
            <motion.h1
              initial={{ opacity: 0, y: 28 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.1 }}
              className="text-[2.75rem] sm:text-6xl lg:text-7xl font-bold text-foreground font-display leading-[1.05] tracking-tight"
            >
              All you need to manage
              <br />
              <span className="text-secondary">church operations</span>
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.25 }}
              className="mt-6 text-base sm:text-lg text-muted-foreground leading-relaxed max-w-lg"
            >
              Finances, members, and communications for ELCT, Roman Catholic, SDA, and Pentecostal churches. All in one place.
            </motion.p>

            {/* Denomination cards */}
            <motion.div
              initial={{ opacity: 0, y: 24 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.55 }}
              className="mt-14 grid grid-cols-2 sm:grid-cols-4 gap-3"
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
                    className={`group flex items-center gap-3 p-4 rounded-2xl border ${d.color} transition-all duration-300 hover:shadow-md`}
                  >
                    <img src={d.icon} alt={d.name} className="w-10 h-10 rounded-xl object-contain" />
                    <div className="flex-1 min-w-0">
                      <span className="text-sm font-bold text-foreground block truncate">{d.name}</span>
                      <span className="text-[10px] text-muted-foreground uppercase tracking-wider">Explore</span>
                    </div>
                    <ArrowRight className="w-3.5 h-3.5 text-muted-foreground group-hover:text-foreground group-hover:translate-x-0.5 transition-all shrink-0" />
                  </Link>
                </motion.div>
              ))}
            </motion.div>
          </div>

          {/* Right column: Stats - modern vertical layout */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7, delay: 0.45 }}
            className="hidden lg:block"
          >
            <div className="relative p-8 rounded-3xl bg-card border border-border shadow-sm">
              {/* Decorative accent line */}
              <div className="absolute top-0 left-8 right-8 h-[2px] bg-gradient-to-r from-transparent via-secondary/40 to-transparent" />
              
              <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-medium mb-8">Platform overview</p>
              
              <div className="space-y-0">
                {stats.map((stat, i) => (
                  <motion.div
                    key={stat.label}
                    initial={{ opacity: 0, x: 16 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: 0.6 + i * 0.12 }}
                    className={`flex items-center gap-5 py-6 ${
                      i < stats.length - 1 ? "border-b border-border" : ""
                    }`}
                  >
                    <div className="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center shrink-0">
                      <stat.icon className="w-5 h-5 text-secondary" />
                    </div>
                    <div className="flex-1">
                      <div className="text-4xl font-bold text-foreground font-display leading-none tracking-tight">{stat.value}</div>
                      <div className="text-xs text-muted-foreground mt-1.5">{stat.label}</div>
                    </div>
                  </motion.div>
                ))}
              </div>

              {/* Bottom accent */}
              <div className="mt-6 pt-6 border-t border-border">
                <p className="text-xs text-muted-foreground">Trusted across Tanzania</p>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
