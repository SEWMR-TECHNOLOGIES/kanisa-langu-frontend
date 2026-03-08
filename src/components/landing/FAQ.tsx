import { motion } from "framer-motion";
import { useState } from "react";
import { ChevronDown } from "lucide-react";

const faqs = [
  {
    q: "What features does Kanisa Langu offer?",
    a: "Kanisa Langu includes revenue tracking, expense management, insightful reporting, member account management, SMS notifications, and integrated mobile payments.",
  },
  {
    q: "How do I get started with Kanisa Langu?",
    a: "Download the app from Google Play or the App Store, create an account, and follow the setup wizard to configure your church structure and start managing operations.",
  },
  {
    q: "Can I customize my reports?",
    a: "Yes, our platform allows you to generate and customize financial, attendance, and operational reports to meet your specific parish needs.",
  },
  {
    q: "Is there a mobile app available?",
    a: "Yes! Kanisa Langu is available on both Android (Google Play) and iOS (App Store) with full feature parity.",
  },
  {
    q: "What types of payment methods are supported?",
    a: "We support mobile money (M-Pesa, Tigo Pesa, Airtel Money), credit/debit cards, and bank transfers for seamless donation processing.",
  },
  {
    q: "Can multiple users access the system?",
    a: "Absolutely. Kanisa Langu supports multiple roles including admin, secretary, accountant, pastor, and evangelist — each with customizable permissions.",
  },
  {
    q: "Is data backup included?",
    a: "Yes, we provide automatic daily backups with enterprise-grade encryption to ensure your data is always secure and recoverable.",
  },
  {
    q: "How can I contact customer support?",
    a: "Reach our support team via email, phone, or through the in-app support portal. We typically respond within 24 hours.",
  },
];

function FaqItem({ faq }: { faq: { q: string; a: string } }) {
  const [open, setOpen] = useState(false);
  return (
    <div className="border-b border-border last:border-0">
      <button
        onClick={() => setOpen(!open)}
        className="w-full flex items-center justify-between py-5 text-left"
      >
        <span className="text-base font-semibold text-foreground pr-4">{faq.q}</span>
        <ChevronDown className={`w-5 h-5 text-muted-foreground shrink-0 transition-transform ${open ? "rotate-180" : ""}`} />
      </button>
      <motion.div
        initial={false}
        animate={{ height: open ? "auto" : 0, opacity: open ? 1 : 0 }}
        className="overflow-hidden"
      >
        <p className="pb-5 text-sm text-muted-foreground leading-relaxed">{faq.a}</p>
      </motion.div>
    </div>
  );
}

export default function FAQ() {
  return (
    <section id="faq" className="py-28 bg-muted/40">
      <div className="max-w-4xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="text-center mb-16"
        >
          <span className="text-sm font-semibold text-secondary uppercase tracking-widest">
            FAQ
          </span>
          <h2 className="mt-3 text-4xl sm:text-5xl font-extrabold text-foreground tracking-tight">
            Frequently Asked{" "}
            <span className="font-serif italic text-gradient-primary">Questions</span>
          </h2>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="bg-card rounded-3xl border border-border p-8 sm:p-10 shadow-sm"
        >
          {faqs.map((faq) => (
            <FaqItem key={faq.q} faq={faq} />
          ))}
        </motion.div>
      </div>
    </section>
  );
}
