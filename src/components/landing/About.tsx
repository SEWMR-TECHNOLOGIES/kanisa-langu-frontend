import { motion } from "framer-motion";

const metrics = [
  { value: "148M+", label: "TZS Managed", sub: "Total revenue tracked" },
  { value: "12,847", label: "Active Members", sub: "Across all churches" },
  { value: "99.9%", label: "Uptime", sub: "Enterprise reliability" },
  { value: "24/7", label: "Support", sub: "Always available" },
];

export default function About() {
  return (
    <section id="about" className="py-28">
      <div className="max-w-7xl mx-auto px-6">
        <div className="grid lg:grid-cols-2 gap-20 items-center">
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
          >
            <span className="text-sm font-bold text-secondary uppercase tracking-widest">
              Why Kanisa Langu
            </span>
            <h2 className="mt-4 text-4xl sm:text-5xl font-bold text-foreground font-display tracking-tight leading-tight">
              Built by people who understand the church
            </h2>
            <p className="mt-6 text-lg text-muted-foreground leading-relaxed">
              We know the unique challenges of managing church operations in Tanzania. Kanisa Langu was built in partnership with church leaders to solve real problems, not hypothetical ones.
            </p>
            <p className="mt-4 text-base text-muted-foreground leading-relaxed">
              Whether you're managing a single parish or overseeing an entire diocese, our platform adapts to your church's structure and grows with your congregation.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="grid grid-cols-2 gap-4"
          >
            {metrics.map((metric, i) => (
              <motion.div
                key={metric.label}
                initial={{ opacity: 0, scale: 0.9 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
                className="p-6 rounded-2xl bg-card border border-border hover:border-secondary/20 hover:shadow-lg transition-all duration-300"
              >
                <div className="text-3xl font-bold text-foreground font-display">{metric.value}</div>
                <div className="text-sm font-semibold text-foreground mt-1">{metric.label}</div>
                <div className="text-xs text-muted-foreground mt-1">{metric.sub}</div>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </div>
    </section>
  );
}