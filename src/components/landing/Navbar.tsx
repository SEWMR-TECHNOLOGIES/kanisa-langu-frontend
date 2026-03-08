import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Link } from "react-router-dom";
import { Menu, X, ChevronRight } from "lucide-react";
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
    const onScroll = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  const scrollTo = (href: string) => {
    setMobileOpen(false);
    const el = document.querySelector(href);
    el?.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <>
      <motion.header
        initial={{ y: -100 }}
        animate={{ y: 0 }}
        transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
        className="fixed top-0 left-0 right-0 z-50"
      >
        {/* Outer container with padding for floating effect */}
        <div className={`transition-all duration-500 ${scrolled ? "pt-2 px-4 lg:px-8" : "pt-4 px-4 lg:px-8"}`}>
          <div
            className={`max-w-6xl mx-auto transition-all duration-500 rounded-2xl ${
              scrolled
                ? "bg-card/90 backdrop-blur-2xl border border-border shadow-md"
                : "bg-card/60 backdrop-blur-xl border border-border/50 shadow-sm"
            }`}
          >
            <div className="flex items-center justify-between h-16 px-6">
              {/* Logo */}
              <Link to="/" className="flex items-center gap-2.5 group">
                <div className="relative">
                  <img src={logo} alt="Kanisa Langu" className="h-8 w-8 relative z-10" />
                  <div className="absolute inset-0 bg-secondary/20 rounded-full blur-lg opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
                <span className="text-[15px] font-semibold tracking-tight text-foreground">
                  Kanisa Langu
                </span>
              </Link>

              {/* Center nav - pill style */}
              <nav className="hidden md:flex items-center">
                <div className="flex items-center gap-0.5 px-1 py-1 rounded-xl bg-muted/50">
                  {navLinks.map((link) => (
                    <button
                      key={link.href}
                      onClick={() => scrollTo(link.href)}
                      className="relative px-4 py-1.5 text-[13px] font-medium text-muted-foreground hover:text-foreground rounded-lg transition-all duration-200 hover:bg-muted"
                    >
                      {link.label}
                    </button>
                  ))}
                </div>
              </nav>

              {/* CTA */}
              <div className="hidden md:flex items-center gap-3">
                <a
                  href="https://play.google.com/store/apps/details?id=com.sewmrtechnologies.kanisa_langu"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="group flex items-center gap-2 px-5 py-2 text-[13px] font-semibold bg-primary text-primary-foreground rounded-xl hover:bg-primary/90 transition-all duration-200"
                >
                  Get Started
                  <ChevronRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
                </a>
              </div>

              {/* Mobile toggle */}
              <button
                onClick={() => setMobileOpen(!mobileOpen)}
                className="md:hidden relative w-10 h-10 flex items-center justify-center rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
              >
                <AnimatePresence mode="wait">
                  {mobileOpen ? (
                    <motion.div key="close" initial={{ opacity: 0, rotate: -90 }} animate={{ opacity: 1, rotate: 0 }} exit={{ opacity: 0, rotate: 90 }} transition={{ duration: 0.15 }}>
                      <X className="w-5 h-5" />
                    </motion.div>
                  ) : (
                    <motion.div key="menu" initial={{ opacity: 0, rotate: 90 }} animate={{ opacity: 1, rotate: 0 }} exit={{ opacity: 0, rotate: -90 }} transition={{ duration: 0.15 }}>
                      <Menu className="w-5 h-5" />
                    </motion.div>
                  )}
                </AnimatePresence>
              </button>
            </div>
          </div>
        </div>
      </motion.header>

      {/* Mobile menu - full overlay */}
      <AnimatePresence>
        {mobileOpen && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              transition={{ duration: 0.2 }}
              className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
              onClick={() => setMobileOpen(false)}
            />
            <motion.div
              initial={{ opacity: 0, y: -20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
              transition={{ duration: 0.3, ease: [0.16, 1, 0.3, 1] }}
              className="fixed top-20 left-4 right-4 z-50 bg-[hsl(220,30%,8%)] border border-white/[0.08] rounded-2xl overflow-hidden shadow-2xl shadow-black/40"
            >
              <div className="p-4 space-y-1">
                {navLinks.map((link, i) => (
                  <motion.button
                    key={link.href}
                    initial={{ opacity: 0, x: -12 }}
                    animate={{ opacity: 1, x: 0 }}
                    transition={{ delay: i * 0.05 + 0.1 }}
                    onClick={() => scrollTo(link.href)}
                    className="flex items-center justify-between w-full text-left text-[15px] font-medium text-white/80 hover:text-white py-3.5 px-4 rounded-xl hover:bg-white/[0.04] transition-colors"
                  >
                    {link.label}
                    <ChevronRight className="w-4 h-4 text-white/20" />
                  </motion.button>
                ))}
              </div>
              <div className="p-4 pt-2 border-t border-white/[0.06]">
                <a
                  href="https://play.google.com/store/apps/details?id=com.elerai.sewmr.kanisa_langu"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center justify-center gap-2 w-full py-3 text-sm font-semibold bg-white text-[hsl(220,30%,6%)] rounded-xl hover:bg-white/90 transition-colors"
                >
                  Get Started
                  <ChevronRight className="w-4 h-4" />
                </a>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>
    </>
  );
}
