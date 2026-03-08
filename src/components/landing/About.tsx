import { motion } from "framer-motion";
import { Check } from "lucide-react";

const points = [
  "Streamlined parish financial tracking",
  "Centralized member database & engagement",
  "Customizable reports for every department",
  "Secure integrated payment processing",
  "Multi-role access for admins and staff",
  "Real-time SMS & notification system",
];

export default function About() {
  return (
    <section id="about" className="py-28 bg-muted/40">
      <div className="max-w-7xl mx-auto px-6">
        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Left visual */}
          <motion.div
            initial={{ opacity: 0, x: -40 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.7 }}
            className="relative"
          >
            <div className="aspect-square max-w-md mx-auto relative">
              {/* Big card */}
              <div className="absolute inset-6 rounded-3xl bg-gradient-to-br from-primary to-primary/80 shadow-2xl shadow-primary/30 flex items-end p-8">
                <div>
                  <div className="text-primary-foreground/60 text-sm font-medium mb-1">Total Revenue</div>
                  <div className="text-4xl font-extrabold text-primary-foreground">TZS 148M</div>
                  <div className="flex items-center gap-1.5 mt-2">
                    <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-white/20 text-primary-foreground">+23%</span>
                    <span className="text-primary-foreground/60 text-xs">vs last year</span>
                  </div>
                </div>
              </div>
              {/* Floating mini card */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: 0.4 }}
                className="absolute -right-4 top-12 glass rounded-2xl p-4 shadow-xl"
              >
                <div className="text-xs text-muted-foreground mb-1">Active Members</div>
                <div className="text-2xl font-bold text-foreground">12,847</div>
                <div className="flex mt-2 -space-x-2">
                  {[...Array(4)].map((_, i) => (
                    <div key={i} className="w-7 h-7 rounded-full bg-gradient-to-br from-primary/70 to-secondary/70 border-2 border-white" />
                  ))}
                  <div className="w-7 h-7 rounded-full bg-muted border-2 border-white flex items-center justify-center text-[10px] font-bold text-muted-foreground">+8K</div>
                </div>
              </motion.div>
            </div>
          </motion.div>

          {/* Right text */}
          <motion.div
            initial={{ opacity: 0, x: 40 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.7 }}
          >
            <span className="text-sm font-semibold text-secondary uppercase tracking-widest">
              About Kanisa Langu
            </span>
            <h2 className="mt-3 text-4xl sm:text-5xl font-extrabold text-foreground tracking-tight leading-tight">
              Built for{" "}
              <span className="font-serif italic text-gradient-primary">Modern</span>{" "}
              Churches
            </h2>
            <p className="mt-5 text-lg text-muted-foreground leading-relaxed">
              Kanisa Langu simplifies every aspect of church operations. Whether you're managing a single parish or an entire diocese, our platform scales with you.
            </p>

            <div className="mt-8 space-y-4">
              {points.map((point) => (
                <div key={point} className="flex items-start gap-3">
                  <div className="mt-0.5 w-5 h-5 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <Check className="w-3 h-3 text-primary" />
                  </div>
                  <span className="text-sm text-foreground font-medium">{point}</span>
                </div>
              ))}
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
