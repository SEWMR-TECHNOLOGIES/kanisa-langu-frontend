import { useState, useRef, useEffect, useCallback } from "react";
import { createPortal } from "react-dom";
import { CalendarIcon, ChevronLeft, ChevronRight } from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";
import { format, startOfMonth, endOfMonth, eachDayOfInterval, addMonths, subMonths, isSameDay, getDay } from "date-fns";

interface ModernDatePickerProps {
  value?: string; // YYYY-MM-DD
  onChange?: (value: string) => void;
  placeholder?: string;
  name?: string;
  required?: boolean;
  className?: string;
}

export default function ModernDatePicker({
  value,
  onChange,
  placeholder = "Pick a date",
  name,
  required: _required,
  className = "",
}: ModernDatePickerProps) {
  const [open, setOpen] = useState(false);
  const [internalValue, setInternalValue] = useState(value || "");
  const ref = useRef<HTMLDivElement>(null);
  const btnRef = useRef<HTMLButtonElement>(null);
  const [dropdownPos, setDropdownPos] = useState<{ top: number; left: number; openUp: boolean }>({ top: 0, left: 0, openUp: false });

  const currentValue = value !== undefined ? value : internalValue;
  const selectedDate = currentValue ? new Date(currentValue + "T00:00:00") : null;
  const [viewMonth, setViewMonth] = useState(() => selectedDate || new Date());

  const updatePosition = useCallback(() => {
    if (!btnRef.current) return;
    const rect = btnRef.current.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const calHeight = 340;
    const openUp = spaceBelow < calHeight && rect.top > calHeight;
    setDropdownPos({
      top: openUp ? rect.top - calHeight - 6 : rect.bottom + 6,
      left: rect.left,
      openUp,
    });
  }, []);

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        const portal = document.getElementById("datepicker-portal");
        if (portal && portal.contains(e.target as Node)) return;
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", handler);
    return () => document.removeEventListener("mousedown", handler);
  }, []);

  useEffect(() => {
    if (open) {
      updatePosition();
      window.addEventListener("scroll", updatePosition, true);
      window.addEventListener("resize", updatePosition);
      return () => {
        window.removeEventListener("scroll", updatePosition, true);
        window.removeEventListener("resize", updatePosition);
      };
    }
  }, [open, updatePosition]);

  const days = eachDayOfInterval({
    start: startOfMonth(viewMonth),
    end: endOfMonth(viewMonth),
  });

  const startDay = getDay(startOfMonth(viewMonth));
  const blanks = Array.from({ length: startDay }, (_, i) => i);

  const selectDate = (date: Date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");
    const val = `${y}-${m}-${d}`;
    if (value === undefined) setInternalValue(val);
    onChange?.(val);
    setOpen(false);
  };

  const weekDays = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];

  // Ensure portal root exists
  let portalRoot = document.getElementById("datepicker-portal");
  if (!portalRoot) {
    portalRoot = document.createElement("div");
    portalRoot.id = "datepicker-portal";
    document.body.appendChild(portalRoot);
  }

  return (
    <div ref={ref} className={`relative ${className}`}>
      <input type="hidden" name={name} value={currentValue} />
      <button
        ref={btnRef}
        type="button"
        onClick={() => setOpen(!open)}
        className={`admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all flex items-center gap-3 text-left ${
          !currentValue ? "text-admin-text/40" : "text-admin-text-bright"
        } ${open ? "ring-2 ring-admin-accent/40 border-admin-accent/40" : ""}`}
      >
        <CalendarIcon className="w-4 h-4 text-admin-text/50 flex-shrink-0" />
        <span className="truncate">
          {selectedDate ? format(selectedDate, "MMM d, yyyy") : placeholder}
        </span>
      </button>
      {createPortal(
        <AnimatePresence>
          {open && (
            <motion.div
              initial={{ opacity: 0, y: dropdownPos.openUp ? 4 : -4, scale: 0.98 }}
              animate={{ opacity: 1, y: 0, scale: 1 }}
              exit={{ opacity: 0, y: dropdownPos.openUp ? 4 : -4, scale: 0.98 }}
              transition={{ duration: 0.15 }}
              style={{ position: "fixed", top: dropdownPos.top, left: dropdownPos.left, zIndex: 9999 }}
              className="w-72 rounded-xl border border-admin-border/40 bg-admin-surface shadow-xl shadow-black/10 p-4"
            >
              {/* Month Nav */}
              <div className="flex items-center justify-between mb-3">
                <button
                  type="button"
                  onClick={() => setViewMonth(subMonths(viewMonth, 1))}
                  className="p-1.5 rounded-lg hover:bg-admin-surface-hover text-admin-text transition-colors"
                >
                  <ChevronLeft className="w-4 h-4" />
                </button>
                <span className="text-sm font-semibold text-admin-text-bright">
                  {format(viewMonth, "MMMM yyyy")}
                </span>
                <button
                  type="button"
                  onClick={() => setViewMonth(addMonths(viewMonth, 1))}
                  className="p-1.5 rounded-lg hover:bg-admin-surface-hover text-admin-text transition-colors"
                >
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>

              {/* Weekday headers */}
              <div className="grid grid-cols-7 mb-1">
                {weekDays.map((d) => (
                  <div key={d} className="text-center text-[10px] font-semibold text-admin-text/50 uppercase py-1">
                    {d}
                  </div>
                ))}
              </div>

              {/* Days grid */}
              <div className="grid grid-cols-7">
                {blanks.map((i) => (
                  <div key={`blank-${i}`} />
                ))}
                {days.map((day) => {
                  const isSelected = selectedDate && isSameDay(day, selectedDate);
                  const isToday = isSameDay(day, new Date());
                  return (
                    <button
                      key={day.toISOString()}
                      type="button"
                      onClick={() => selectDate(day)}
                      className={`w-9 h-9 rounded-lg text-xs font-medium transition-all duration-150 mx-auto flex items-center justify-center ${
                        isSelected
                          ? "bg-admin-accent text-white shadow-md shadow-admin-accent/25"
                          : isToday
                          ? "bg-admin-accent/10 text-admin-accent font-semibold"
                          : "text-admin-text-bright hover:bg-admin-surface-hover"
                      }`}
                    >
                      {day.getDate()}
                    </button>
                  );
                })}
              </div>
            </motion.div>
          )}
        </AnimatePresence>,
        portalRoot
      )}
    </div>
  );
}
