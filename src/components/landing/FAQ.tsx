import { motion } from "framer-motion";
import { useState } from "react";
import { Plus, Minus } from "lucide-react";

const faqs = [
  { q: "Which church denominations does Kanisa Langu support?", a: "We support ELCT (Evangelical Lutheran Church in Tanzania), Roman Catholic, SDA (Seventh-Day Adventist), and Pentecostal churches. Each denomination gets tailored features for their specific structure and needs." },
  { q: "How do I get started?", a: "Download the app from Google Play or App Store, create an account, and follow the setup wizard. You can configure your church structure and start managing in minutes." },
  { q: "What payment methods are supported?", a: "We support M-Pesa, Tigo Pesa, Airtel Money, credit/debit cards, and bank transfers for seamless donation and offering processing." },
  { q: "Can multiple users access the system?", a: "Yes. Kanisa Langu supports role-based access with roles like admin, pastor, secretary, accountant, and evangelist — each with customizable permissions." },
  { q: "Is my church data secure?", a: "Absolutely. We use enterprise-grade encryption, automated daily backups, and strict access controls to ensure your data is always protected." },
  { q: "Can I manage multiple parishes or branches?", a: "Yes. Whether you're managing a single parish or an entire diocese with multiple branches, our platform scales with your structure." },
  { q: "Is customer support available?", a: "Our support team is available via email, phone, and in-app support portal. We typically respond within 24 hours." },
];

function FaqItem({ faq, index }: { faq: { q: string; a: string }; index: number }) {
  const [open, setOpen] = useState(false);
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true }}
      transition={{ delay: index * 0.05 }}
      className="border-b border-border last:border-0"
    >
      <button
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between py-6 text-left group"
      >
        <span className="text-base font-semibold text-foreground pr-8 group-hover:text-secondary transition-colors">{faq.q}</span>
        <div className={`w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors ${open ? "bg-secondary text-secondary-foreground" : "bg-muted text-muted-foreground"}`}>
          {open ? <Minus className="w-4 h-4" /> : <Plus className="w-4 h-4" />}
        </div>
      </button>
      <motion.div
        initial={false}
        animate={{ height: open ? "auto" : 0, opacity: open ? 1 : 0 }}
        className="overflow-hidden"
      >
        <p className="pb-6 text-sm text-muted-foreground leading-relaxed max-w-2xl">{faq.a}</p>
      </motion.div>
    </motion.div>
  );
}

export default function FAQ() {
  return (
    <section id="faq" className="py-28">
      <div className="max-w-3xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="mb-14"
        >
          <span className="text-sm font-bold text-secondary uppercase tracking-widest">FAQ</span>
          <h2 className="mt-4 text-4xl sm:text-5xl font-bold text-foreground font-display tracking-tight">
            Common questions
          </h2>
        </motion.div>

        <div className="bg-card rounded-3xl border border-border p-8 sm:p-10">
          {faqs.map((faq, i) => (
            <FaqItem key={faq.q} faq={faq} index={i} />
          ))}
        </div>
      </div>
    </section>
  );
}