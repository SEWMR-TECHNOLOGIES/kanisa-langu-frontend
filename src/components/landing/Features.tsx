import { motion } from "framer-motion";
import {
  TrendingUp, Wallet, PieChart, Users, CreditCard, Shield, Bell, Smartphone
} from "lucide-react";

const features = [
  {
    icon: TrendingUp,
    title: "Revenue Tracking",
    description: "Record and monitor all parish income streams with full transparency and real-time dashboards.",
    color: "bg-blue-50 text-blue-600",
  },
  {
    icon: Wallet,
    title: "Expense Management",
    description: "Track and manage all expenses with categorization for better financial planning and budgeting.",
    color: "bg-amber-50 text-amber-600",
  },
  {
    icon: PieChart,
    title: "Insightful Reports",
    description: "Generate customizable reports to gain deep insights into financial and operational performance.",
    color: "bg-violet-50 text-violet-600",
  },
  {
    icon: Users,
    title: "Member Management",
    description: "Dedicated accounts for effective engagement, tracking attendance, and support for every member.",
    color: "bg-emerald-50 text-emerald-600",
  },
  {
    icon: CreditCard,
    title: "Integrated Payments",
    description: "Facilitate donations and payments seamlessly through our secure mobile money and card system.",
    color: "bg-rose-50 text-rose-600",
  },
  {
    icon: Shield,
    title: "Data Security",
    description: "Enterprise-grade encryption and regular backups ensure your church data is always protected.",
    color: "bg-cyan-50 text-cyan-600",
  },
  {
    icon: Bell,
    title: "SMS & Notifications",
    description: "Keep your congregation informed with targeted SMS campaigns and push notifications.",
    color: "bg-orange-50 text-orange-600",
  },
  {
    icon: Smartphone,
    title: "Mobile First",
    description: "Access everything on the go with our native iOS and Android apps built for church leaders.",
    color: "bg-indigo-50 text-indigo-600",
  },
];

const container = {
  hidden: {},
  show: { transition: { staggerChildren: 0.08 } },
};

const item = {
  hidden: { opacity: 0, y: 30 },
  show: { opacity: 1, y: 0, transition: { duration: 0.5 } },
};

export default function Features() {
  return (
    <section id="features" className="py-28 relative feature-glow">
      <div className="max-w-7xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          className="text-center max-w-2xl mx-auto mb-16"
        >
          <span className="text-sm font-semibold text-secondary uppercase tracking-widest">
            Powerful Tools
          </span>
          <h2 className="mt-3 text-4xl sm:text-5xl font-extrabold text-foreground tracking-tight">
            Everything Your{" "}
            <span className="font-serif italic text-gradient-primary">Church</span>{" "}
            Needs
          </h2>
          <p className="mt-5 text-lg text-muted-foreground leading-relaxed">
            Discover the powerful tools and features that make Kanisa Langu the ultimate solution for parish management.
          </p>
        </motion.div>

        <motion.div
          variants={container}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, margin: "-100px" }}
          className="grid sm:grid-cols-2 lg:grid-cols-4 gap-5"
        >
          {features.map((feat) => (
            <motion.div
              key={feat.title}
              variants={item}
              className="group relative bg-card rounded-2xl border border-border p-7 hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 hover:-translate-y-1"
            >
              <div className={`w-12 h-12 rounded-xl ${feat.color} flex items-center justify-center mb-5`}>
                <feat.icon className="w-6 h-6" />
              </div>
              <h3 className="text-lg font-bold text-foreground mb-2">{feat.title}</h3>
              <p className="text-sm text-muted-foreground leading-relaxed">{feat.description}</p>
            </motion.div>
          ))}
        </motion.div>
      </div>
    </section>
  );
}
