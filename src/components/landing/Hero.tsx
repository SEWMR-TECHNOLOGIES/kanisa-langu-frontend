import { motion } from "framer-motion";
import heroImage from "@/assets/hero-church.png";

export default function Hero() {
  return (
    <section className="relative min-h-screen flex items-center overflow-hidden hero-glow pt-20">
      {/* Decorative orbs */}
      <div className="absolute top-20 left-10 w-72 h-72 rounded-full bg-primary/5 blur-3xl" />
      <div className="absolute bottom-20 right-10 w-96 h-96 rounded-full bg-secondary/10 blur-3xl" />

      <div className="max-w-7xl mx-auto px-6 w-full">
        <div className="grid lg:grid-cols-2 gap-12 lg:gap-6 items-center">
          {/* Text */}
          <motion.div
            initial={{ opacity: 0, y: 40 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="max-w-xl"
          >
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-accent border border-border mb-8">
              <span className="w-2 h-2 rounded-full bg-secondary animate-pulse" />
              <span className="text-xs font-semibold text-accent-foreground uppercase tracking-wider">
                Church Management Platform
              </span>
            </div>

            <h1 className="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-[1.05] tracking-tight text-foreground">
              Simplify{" "}
              <span className="font-serif italic text-gradient-primary">Church</span>
              <br />
              Management with{" "}
              <span className="text-gradient-gold">Kanisa Langu</span>
            </h1>

            <p className="mt-6 text-lg text-muted-foreground leading-relaxed max-w-md">
              Your comprehensive solution for managing head parish operations — from tracking revenues to engaging with church members.
            </p>

            <div className="flex flex-wrap gap-4 mt-10">
              <a
                href="#features"
                onClick={(e) => {
                  e.preventDefault();
                  document.querySelector("#features")?.scrollIntoView({ behavior: "smooth" });
                }}
                className="px-8 py-4 bg-primary text-primary-foreground rounded-full font-semibold text-sm shadow-xl shadow-primary/30 hover:shadow-2xl hover:shadow-primary/40 hover:-translate-y-0.5 transition-all"
              >
                Explore Features
              </a>
              <a
                href="#about"
                onClick={(e) => {
                  e.preventDefault();
                  document.querySelector("#about")?.scrollIntoView({ behavior: "smooth" });
                }}
                className="px-8 py-4 bg-card text-foreground rounded-full font-semibold text-sm border border-border hover:border-primary/30 hover:-translate-y-0.5 transition-all"
              >
                Learn More
              </a>
            </div>

            {/* Stats */}
            <div className="flex gap-10 mt-14">
              {[
                { value: "500+", label: "Churches" },
                { value: "50K+", label: "Members" },
                { value: "99.9%", label: "Uptime" },
              ].map((stat) => (
                <div key={stat.label}>
                  <div className="text-2xl font-extrabold text-foreground">{stat.value}</div>
                  <div className="text-sm text-muted-foreground">{stat.label}</div>
                </div>
              ))}
            </div>
          </motion.div>

          {/* Hero image */}
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 1, ease: "easeOut", delay: 0.2 }}
            className="relative flex justify-center lg:justify-end"
          >
            <div className="relative">
              <img
                src={heroImage}
                alt="Kanisa Langu Platform"
                className="w-full max-w-lg animate-float drop-shadow-2xl"
              />
              {/* Floating badge */}
              <motion.div
                initial={{ opacity: 0, x: -30 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 1, duration: 0.5 }}
                className="absolute -left-4 bottom-1/4 glass rounded-2xl px-4 py-3 shadow-xl"
              >
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <svg className="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div>
                    <div className="text-xs text-muted-foreground">Revenue Today</div>
                    <div className="text-sm font-bold text-foreground">TZS 2.4M</div>
                  </div>
                </div>
              </motion.div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
