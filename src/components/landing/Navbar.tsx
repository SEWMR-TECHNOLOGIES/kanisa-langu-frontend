import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Link } from "react-router-dom";
import { Menu, X } from "lucide-react";
import logo from "../../assets/logo.png";

const navLinks = [
  { label: "Churches", href: "#churches" },
  { label: "Features", href: "#features" },
  { label: "About", href: "#about" },
  { label: "FAQ", href: "#faq" },
];

export default function Navbar() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const scrollTo = (href: string) => {
    setMobileOpen(false);
    const el = document.querySelector(href);
    el?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <motion.header
      initial={{ y: -80 }}
      animate={{ y: 0 }}
      transition={{ duration: 0.5 }}
      className="fixed top-0 left-0 right-0 z-50 bg-transparent"
    >
      <div className="max-w-7xl mx-auto px-6 flex items-center justify-between h-20">
        <Link to="/" className="flex items-center gap-3">
          <img src={logo} alt="Kanisa Langu" className="h-9 w-9" />
          <span className={`text-lg font-bold tracking-tight transition-colors ${scrolled ? "text-foreground" : "text-white"}`}>
            Kanisa Langu
          </span>
        </Link>

        <nav className="hidden md:flex items-center gap-1">
          {navLinks.map((link) => (
            <button
              key={link.href}
              onClick={() => scrollTo(link.href)}
              className={`px-4 py-2 text-sm font-medium rounded-xl transition-colors ${
                scrolled
                  ? "text-muted-foreground hover:text-foreground hover:bg-muted"
                  : "text-white/60 hover:text-white hover:bg-white/10"
              }`}
            >
              {link.label}
            </button>
          ))}
        </nav>

        <div className="hidden md:flex items-center gap-3">
          <a
            href="https://play.google.com/store/apps/details?id=com.elerai.sewmr.kanisa_langu"
            target="_blank"
            rel="noopener noreferrer"
            className="px-5 py-2.5 text-sm font-bold bg-secondary text-secondary-foreground rounded-xl hover:brightness-110 transition-all shadow-lg shadow-secondary/20"
          >
            Download App
          </a>
        </div>

        <button
          onClick={() => setMobileOpen(!mobileOpen)}
          className={`md:hidden p-2 rounded-xl ${scrolled ? "text-foreground" : "text-white"}`}
        >
          {mobileOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
        </button>
      </div>

      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            className="md:hidden bg-card/95 backdrop-blur-xl border-t border-border overflow-hidden"
          >
            <div className="px-6 py-6 space-y-2">
              {navLinks.map((link) => (
                <button
                  key={link.href}
                  onClick={() => scrollTo(link.href)}
                  className="block w-full text-left text-base font-medium text-foreground py-3 px-4 rounded-xl hover:bg-muted transition-colors"
                >
                  {link.label}
                </button>
              ))}
              <a
                href="https://play.google.com/store/apps/details?id=com.elerai.sewmr.kanisa_langu"
                target="_blank"
                rel="noopener noreferrer"
                className="block text-center mt-4 px-5 py-3 text-sm font-bold bg-secondary text-secondary-foreground rounded-xl"
              >
                Download App
              </a>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.header>
  );
}