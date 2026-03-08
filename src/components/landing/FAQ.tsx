import { motion, AnimatePresence } from "framer-motion";
import { useState } from "react";
import { ChevronDown } from "lucide-react";

const faqs = [
  { q: "Which church denominations does Kanisa Langu support?", a: "We support ELCT (Evangelical Lutheran Church in Tanzania), Roman Catholic, SDA (Seventh-Day Adventist), and Pentecostal churches. Each denomination gets tailored features for their specific structure and needs." },
  { q: "How do I get started?", a: "Download the app from Google Play or App Store, create an account, and follow the setup wizard. You can configure your church structure and start managing in minutes." },
  { q: "What payment methods are supported?", a: "We support M-Pesa, Tigo Pesa, Airtel Money, credit/debit cards, and bank transfers for seamless donation and offering processing." },
  { q: "Can multiple users access the system?", a: "Yes. Kanisa Langu supports role-based access with roles like admin, pastor, secretary, accountant, and evangelist, each with customizable permissions." },
  { q: "Is my church data secure?", a: "Absolutely. We use enterprise-grade encryption, automated daily backups, and strict access controls to ensure your data is always protected." },
  { q: "Can I manage multiple parishes or branches?", a: "Yes. Whether you're managing a single parish or an entire diocese with multiple branches, our platform scales with your structure." },
  { q: "Is customer support available?", a: "Our support team is available via email, phone, and in-app support portal. We typically respond within 24 hours." },
];

function FaqItem({ faq, index, isOpen, onToggle }: { faq: { q: string; a: string }; index: number; isOpen: boolean; onToggle: () => void }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.04, duration: 0.4 }}
    >
      <button
        onClick={onToggle}
        className={`w-full text-left p-5 sm:p-6 rounded-2xl transition-all duration-300 group ${
          isOpen
            ? "bg-primary text-primary-foreground shadow-lg shadow-primary/10"
            : "bg-card hover:bg-muted/60 border border-border"
        }`}
      >
        <div className="flex items-start gap-4">
          <span className={`text-xs font-bold tracking-wider mt-1 shrink-0 font-mono ${
            isOpen ? "text-secondary" : "text-muted-foreground"
          }`}>
            {String(index + 1).padStart(2, "0")}
          </span>
          <div className="flex-1 min-w-0">
            <div className="flex items-center justify-between gap-3">
              <h3 className={`text-[15px] sm:text-base font-semibold leading-snug ${
                isOpen ? "text-primary-foreground" : "text-foreground"
              }`}>
                {faq.q}
              </h3>
              <ChevronDown className={`w-4 h-4 shrink-0 transition-transform duration-300 ${
                isOpen ? "rotate-180 text-secondary" : "text-muted-foreground"
              }`} />
            </div>
            <AnimatePresence>
              {isOpen && (
                <motion.p
                  initial={{ height: 0, opacity: 0, marginTop: 0 }}
                  animate={{ height: "auto", opacity: 1, marginTop: 12 }}
                  exit={{ height: 0, opacity: 0, marginTop: 0 }}
                  transition={{ duration: 0.3, ease: "easeInOut" }}
                  className="text-sm leading-relaxed text-primary-foreground/80 overflow-hidden"
                >
                  {faq.a}
                </motion.p>
              )}
            </AnimatePresence>
          </div>
        </div>
      </button>
    </motion.div>
  );
}

export default function FAQ() {
  const [openIndex, setOpenIndex] = useState<number | null>(0);

  const leftColumn = faqs.filter((_, i) => i % 2 === 0);
  const rightColumn = faqs.filter((_, i) => i % 2 !== 0);
  const leftIndices = faqs.map((_, i) => i).filter(i => i % 2 === 0);
  const rightIndices = faqs.map((_, i) => i).filter(i => i % 2 !== 0);

  return (
    <section id="faq" className="py-28">
      <div className="max-w-6xl mx-auto px-6">
        <div className="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-14">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
          >
            <span className="text-sm font-bold text-secondary uppercase tracking-widest">Support</span>
            <h2 className="mt-3 text-4xl sm:text-5xl font-bold text-foreground font-display tracking-tight">
              Frequently asked<br className="hidden sm:block" /> questions
            </h2>
          </motion.div>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-muted-foreground text-sm max-w-md lg:text-right"
          >
            Everything you need to know about Kanisa Langu. Can't find what you're looking for? Reach out to our support team.
          </motion.p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-3">
          <div className="flex flex-col gap-3">
            {leftColumn.map((faq, i) => (
              <FaqItem
                key={faq.q}
                faq={faq}
                index={leftIndices[i]}
                isOpen={openIndex === leftIndices[i]}
                onToggle={() => setOpenIndex(openIndex === leftIndices[i] ? null : leftIndices[i])}
              />
            ))}
          </div>
          <div className="flex flex-col gap-3">
            {rightColumn.map((faq, i) => (
              <FaqItem
                key={faq.q}
                faq={faq}
                index={rightIndices[i]}
                isOpen={openIndex === rightIndices[i]}
                onToggle={() => setOpenIndex(openIndex === rightIndices[i] ? null : rightIndices[i])}
              />
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
