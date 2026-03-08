import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { ArrowRight } from "lucide-react";
import elctIcon from "../../assets/elct-icon.png";
import rcIcon from "../../assets/rc-icon.png";
import sdaIcon from "../../assets/sda-icon.png";
import pentecostalIcon from "../../assets/pentecostal-icon.png";

const churches = [
  {
    slug: "elct",
    name: "ELCT",
    fullName: "Evangelical Lutheran Church in Tanzania",
    icon: elctIcon,
    accent: "group-hover:border-blue-400/40",
    iconBg: "bg-blue-900/10",
    description: "Diocese & parish management tools designed for the Lutheran church structure with multi-level oversight.",
  },
  {
    slug: "roman-catholic",
    name: "Roman Catholic",
    fullName: "Roman Catholic Church",
    icon: rcIcon,
    accent: "group-hover:border-red-400/40",
    iconBg: "bg-red-900/10",
    description: "Sacramental records, parish operations, and diocese reporting for Catholic churches.",
  },
  {
    slug: "sda",
    name: "SDA",
    fullName: "Seventh-Day Adventist",
    icon: sdaIcon,
    accent: "group-hover:border-cyan-400/40",
    iconBg: "bg-cyan-900/10",
    description: "Conference-level tithe management, Sabbath operations, and department coordination.",
  },
  {
    slug: "pentecostal",
    name: "Pentecostal",
    fullName: "Pentecostal Churches",
    icon: pentecostalIcon,
    accent: "group-hover:border-orange-400/40",
    iconBg: "bg-orange-900/10",
    description: "Multi-branch management, cell group tracking, and growth analytics for dynamic ministries.",
  },
];

export default function Churches() {
  return (
    <section id="churches" className="py-28 relative">
      <div className="max-w-7xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="max-w-2xl mb-16"
        >
          <span className="text-sm font-bold text-secondary uppercase tracking-widest">
            Built for every denomination
          </span>
          <h2 className="mt-4 text-4xl sm:text-5xl font-bold text-foreground font-display tracking-tight">
            Choose your church
          </h2>
          <p className="mt-5 text-lg text-muted-foreground leading-relaxed">
            Kanisa Langu is tailored for each denomination. Select your church to explore features designed specifically for your needs.
          </p>
        </motion.div>

        <div className="grid sm:grid-cols-2 gap-5">
          {churches.map((church, i) => (
            <motion.div
              key={church.slug}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.1 }}
            >
              <Link
                to={`/churches/${church.slug}`}
                className={`group block relative p-8 rounded-3xl bg-card border border-border ${church.accent} hover:shadow-2xl hover:-translate-y-1 transition-all duration-500`}
              >
                <div className="flex items-start justify-between mb-6">
                  <div className={`w-16 h-16 rounded-2xl ${church.iconBg} flex items-center justify-center p-2`}>
                    <img src={church.icon} alt={church.name} className="w-full h-full object-contain" />
                  </div>
                  <ArrowRight className="w-5 h-5 text-muted-foreground group-hover:text-secondary group-hover:translate-x-1 transition-all" />
                </div>

                <h3 className="text-xl font-bold text-foreground font-display mb-1">{church.fullName}</h3>
                <span className="text-xs font-semibold text-secondary uppercase tracking-wider">{church.name}</span>
                <p className="mt-4 text-sm text-muted-foreground leading-relaxed">
                  {church.description}
                </p>

                {/* Hover shimmer */}
                <div className="absolute inset-0 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none bg-gradient-to-r from-transparent via-white/3 to-transparent" />
              </Link>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}