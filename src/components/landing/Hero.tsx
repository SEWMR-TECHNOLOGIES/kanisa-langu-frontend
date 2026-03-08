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
        {/* Main content */}
        <div className="max-w-3xl">
          {/* Headline */}
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

          {/* Subtitle */}
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.25 }}
            className="mt-6 text-base sm:text-lg text-muted-foreground leading-relaxed max-w-lg"
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
              className="px-7 py-3.5 bg-primary text-primary-foreground rounded-xl font-bold text-sm hover:-translate-y-0.5 transition-all duration-300 shadow-lg shadow-primary/20"
            >
              Get Started
            </button>
            <button
              onClick={() => scrollTo("#features")}
              className="px-7 py-3.5 text-foreground border border-border rounded-xl font-medium text-sm hover:bg-muted transition-all duration-300"
            >
              Learn More
            </button>
          </motion.div>
        </div>

        {/* Stats bar - inline, modern */}
        <motion.div
          initial={{ opacity: 0, y: 24 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.5 }}
          className="mt-16 flex items-center gap-0 w-fit rounded-2xl bg-card border border-border overflow-hidden shadow-sm"
        >
          {stats.map((stat, i) => (
            <motion.div
              key={stat.label}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.55 + i * 0.1 }}
              className={`flex items-center gap-3 px-6 sm:px-8 py-5 ${
                i < stats.length - 1 ? "border-r border-border" : ""
              }`}
            >
              <stat.icon className="w-5 h-5 text-secondary hidden sm:block" />
              <div>
                <div className="text-2xl sm:text-3xl font-bold text-foreground font-display leading-none">{stat.value}</div>
                <div className="text-[11px] text-muted-foreground uppercase tracking-wider mt-1">{stat.label}</div>
              </div>
            </motion.div>
          ))}
        </motion.div>

        {/* Denomination cards */}
        <motion.div
          initial={{ opacity: 0, y: 24 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6, delay: 0.65 }}
          className="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl"
        >
          {denominations.map((d, i) => (
            <motion.div
              key={d.slug}
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.7 + i * 0.08 }}
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
    </section>
  );
}
