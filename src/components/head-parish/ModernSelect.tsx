import { useState, useRef, useEffect } from "react";
import { ChevronDown, Check } from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";

interface Option {
  value: string;
  label: string;
}

interface ModernSelectProps {
  options: Option[];
  value?: string;
  onChange?: (value: string) => void;
  placeholder?: string;
  name?: string;
  required?: boolean;
  className?: string;
}

export default function ModernSelect({
  options,
  value,
  onChange,
  placeholder = "Select an option",
  name,
  required,
  className = "",
}: ModernSelectProps) {
  const [open, setOpen] = useState(false);
  const [internalValue, setInternalValue] = useState(value || "");
  const ref = useRef<HTMLDivElement>(null);

  const currentValue = value !== undefined ? value : internalValue;
  const selectedLabel = options.find((o) => o.value === currentValue)?.label;

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, []);

  const select = (val: string) => {
    if (value === undefined) setInternalValue(val);
    onChange?.(val);
    setOpen(false);
  };

  return (
    <div ref={ref} className={`relative ${className}`}>
      {/* Hidden input for form submission */}
      <input type="hidden" name={name} value={currentValue} />
      <button
        type="button"
        onClick={() => setOpen(!open)}
        className={`admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all flex items-center justify-between gap-2 text-left ${
          !selectedLabel ? "text-admin-text/40" : "text-admin-text-bright"
        } ${open ? "ring-2 ring-admin-accent/40 border-admin-accent/40" : ""}`}
      >
        <span className="truncate">{selectedLabel || placeholder}</span>
        <ChevronDown
          className={`w-4 h-4 text-admin-text/50 flex-shrink-0 transition-transform duration-200 ${
            open ? "rotate-180" : ""
          }`}
        />
      </button>
      <AnimatePresence>
        {open && (
          <motion.div
            initial={{ opacity: 0, y: -4, scale: 0.98 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -4, scale: 0.98 }}
            transition={{ duration: 0.15 }}
            className="absolute z-50 mt-1.5 w-full rounded-xl border border-admin-border/40 bg-admin-surface shadow-xl shadow-black/10 overflow-hidden max-h-60 overflow-y-auto"
          >
            {options.map((opt) => (
              <button
                key={opt.value}
                type="button"
                onClick={() => select(opt.value)}
                className={`w-full px-4 py-2.5 text-sm text-left flex items-center justify-between gap-2 transition-colors ${
                  currentValue === opt.value
                    ? "bg-admin-accent/10 text-admin-accent font-medium"
                    : "text-admin-text-bright hover:bg-admin-surface-hover"
                }`}
              >
                <span className="truncate">{opt.label}</span>
                {currentValue === opt.value && (
                  <Check className="w-3.5 h-3.5 text-admin-accent flex-shrink-0" />
                )}
              </button>
            ))}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
