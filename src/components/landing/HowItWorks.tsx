import { motion } from "framer-motion";
import { Download, UserPlus, Sparkles } from "lucide-react";

const steps = [
  {
    icon: Download,
    step: "01",
    title: "Download the App",
    description: "Get Kanisa Langu on your Android or iOS device completely free from the app stores.",
  },
  {
    icon: UserPlus,
    step: "02",
    title: "Create an Account",
    description: "Set up your parish profile, invite your team, and configure your church structure.",
  },
  {
    icon: Sparkles,
    step: "03",
    title: "Start Managing",
    description: "Track finances, manage members, send notifications, and generate reports effortlessly.",
  },
];

export default function HowItWorks() {
  return (
    <section id="how-it-works" className="py-28">
      <div className="max-w-7xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="text-center max-w-2xl mx-auto mb-16"
        >
          <span className="text-sm font-semibold text-secondary uppercase tracking-widest">
            Simple Process
          </span>
          <h2 className="mt-3 text-4xl sm:text-5xl font-extrabold text-foreground tracking-tight">
            Get Started in{" "}
            <span className="font-serif italic text-gradient-gold">Minutes</span>
          </h2>
          <p className="mt-5 text-lg text-muted-foreground">
            Three simple steps to transform how your church operates.
          </p>
        </motion.div>

        <div className="grid md:grid-cols-3 gap-8">
          {steps.map((step, i) => (
            <motion.div
              key={step.step}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.15 }}
              className="relative text-center"
            >
              {/* Connector line */}
              {i < steps.length - 1 && (
                <div className="hidden md:block absolute top-16 left-[60%] w-[80%] h-px border-t-2 border-dashed border-border" />
              )}
              <div className="relative inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-accent mb-6">
                <step.icon className="w-10 h-10 text-primary" />
                <span className="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-secondary text-secondary-foreground text-xs font-extrabold flex items-center justify-center shadow-lg">
                  {step.step}
                </span>
              </div>
              <h3 className="text-xl font-bold text-foreground mb-2">{step.title}</h3>
              <p className="text-sm text-muted-foreground leading-relaxed max-w-xs mx-auto">{step.description}</p>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
