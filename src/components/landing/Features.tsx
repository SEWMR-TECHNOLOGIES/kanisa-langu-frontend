import { motion } from "framer-motion";
import { TrendingUp, Wallet, PieChart, Users, CreditCard, Shield, Bell, Smartphone } from "lucide-react";

const features = [
  { icon: TrendingUp, title: "Revenue Tracking", description: "Real-time dashboards for all parish income streams with full transparency." },
  { icon: Wallet, title: "Expense Management", description: "Categorized expense tracking for better financial planning." },
  { icon: PieChart, title: "Smart Reports", description: "Auto-generated insights across all levels of your church structure." },
  { icon: Users, title: "Member Management", description: "Complete member profiles with engagement tracking." },
  { icon: CreditCard, title: "Digital Payments", description: "Mobile money & card payments for offerings and donations." },
  { icon: Shield, title: "Data Security", description: "Enterprise encryption with automated daily backups." },
  { icon: Bell, title: "SMS & Push Alerts", description: "Targeted notifications to your entire congregation." },
  { icon: Smartphone, title: "Mobile First", description: "Native iOS & Android apps for church leaders on the go." },
];

export default function Features() {
  return (
    <section id="features" className="py-28 bg-primary relative overflow-hidden">
      {/* Subtle grid pattern */}
      <div className="absolute inset-0 opacity-[0.03]" style={{
        backgroundImage: "linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px)",
        backgroundSize: "60px 60px"
      }} />

      <div className="max-w-7xl mx-auto px-6 relative z-10">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="max-w-2xl mb-16"
        >
          <span className="text-sm font-bold text-secondary uppercase tracking-widest">
            Platform capabilities
          </span>
          <h2 className="mt-4 text-4xl sm:text-5xl font-bold text-primary-foreground font-display tracking-tight">
            Everything you need, nothing you don't
          </h2>
          <p className="mt-5 text-lg text-primary-foreground/50 leading-relaxed">
            Powerful tools built specifically for church operations in Tanzania.
          </p>
        </motion.div>

        <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {features.map((feat, i) => (
            <motion.div
              key={feat.title}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.06 }}
              className="group p-6 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-white/10 transition-all duration-300"
            >
              <feat.icon className="w-7 h-7 text-secondary mb-4" />
              <h3 className="text-base font-bold text-primary-foreground mb-2">{feat.title}</h3>
              <p className="text-sm text-primary-foreground/40 leading-relaxed">{feat.description}</p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}