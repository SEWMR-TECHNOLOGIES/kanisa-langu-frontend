import { motion } from "framer-motion";

const steps = [
  {
    num: "01",
    title: "Download",
    description: "Get Kanisa Langu free on Android or iOS from the app stores.",
  },
  {
    num: "02",
    title: "Set up your church",
    description: "Configure your church structure, invite team members, and set permissions.",
  },
  {
    num: "03",
    title: "Start managing",
    description: "Track finances, engage members, and generate reports from day one.",
  },
];

export default function HowItWorks() {
  return (
    <section id="how-it-works" className="py-28 bg-muted/50">
      <div className="max-w-7xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="max-w-2xl mb-16"
        >
          <span className="text-sm font-bold text-secondary uppercase tracking-widest">
            Get started
          </span>
          <h2 className="mt-4 text-4xl sm:text-5xl font-bold text-foreground font-display tracking-tight">
            Up and running in minutes
          </h2>
        </motion.div>

        <div className="grid md:grid-cols-3 gap-8">
          {steps.map((step, i) => (
            <motion.div
              key={step.num}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.15 }}
              className="relative"
            >
              {/* Connector line */}
              {i < steps.length - 1 && (
                <div className="hidden md:block absolute top-8 left-[calc(50%+40px)] w-[calc(100%-80px)] h-px border-t-2 border-dashed border-border" />
              )}
              <div className="relative">
                <span className="text-7xl font-bold text-secondary/15 font-display">{step.num}</span>
                <h3 className="text-xl font-bold text-foreground font-display -mt-4">{step.title}</h3>
                <p className="mt-3 text-sm text-muted-foreground leading-relaxed">{step.description}</p>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}