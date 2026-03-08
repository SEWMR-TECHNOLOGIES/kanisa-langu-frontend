import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Save } from "lucide-react";
import ModernSelect from "./ModernSelect";
import ModernDatePicker from "./ModernDatePicker";
import NumberInput from "./NumberInput";
import type { Column } from "./DataTable";

interface EditRecordModalProps<T> {
  isOpen: boolean;
  onClose: () => void;
  onSave: (updated: Partial<T>) => void;
  row: T | null;
  columns: Column<T>[];
  title?: string;
}

export default function EditRecordModal<T extends Record<string, any>>({
  isOpen,
  onClose,
  onSave,
  row,
  columns,
  title = "Edit Record",
}: EditRecordModalProps<T>) {
  const [values, setValues] = useState<Record<string, any>>({});

  useEffect(() => {
    if (row) {
      const v: Record<string, any> = {};
      columns.forEach(col => {
        v[String(col.key)] = row[col.key as keyof T] ?? "";
      });
      setValues(v);
    }
  }, [row, columns]);

  const handleSave = () => {
    onSave(values as Partial<T>);
    onClose();
  };

  return (
    <AnimatePresence>
      {isOpen && row && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"
            onClick={onClose}
          />
          <div className="fixed inset-0 z-[61] flex items-center justify-center p-4">
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 20 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="admin-card rounded-2xl w-full max-w-lg max-h-[85vh] overflow-hidden flex flex-col"
            >
              {/* Header */}
              <div className="flex items-center justify-between px-6 py-4 border-b border-admin-border/30">
                <h2 className="text-base font-bold text-admin-text-bright">{title}</h2>
                <button
                  onClick={onClose}
                  className="p-2 rounded-xl hover:bg-admin-surface-hover text-admin-text transition-colors"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              {/* Body */}
              <div className="flex-1 overflow-y-auto p-6 space-y-4">
                {columns.map((col) => {
                  const key = String(col.key);
                  const val = String(values[key] ?? "");

                  return (
                    <div key={key}>
                      <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
                        {col.label}
                      </label>
                      {col.type === "select" && col.options ? (
                        <ModernSelect
                          options={col.options}
                          value={val}
                          onChange={(v) => setValues({ ...values, [key]: v })}
                        />
                      ) : col.type === "date" ? (
                        <ModernDatePicker
                          value={val}
                          onChange={(v) => setValues({ ...values, [key]: v })}
                        />
                      ) : col.type === "number" ? (
                        <NumberInput
                          value={val}
                          onChange={(v) => setValues({ ...values, [key]: v })}
                        />
                      ) : (
                        <input
                          type="text"
                          value={val}
                          onChange={(e) => setValues({ ...values, [key]: e.target.value })}
                          className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all"
                        />
                      )}
                    </div>
                  );
                })}
              </div>

              {/* Footer */}
              <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-admin-border/30">
                <button
                  onClick={onClose}
                  className="px-5 py-2.5 rounded-xl text-sm font-medium text-admin-text hover:bg-admin-surface-hover transition-colors"
                >
                  Cancel
                </button>
                <button
                  onClick={handleSave}
                  className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold flex items-center gap-2"
                >
                  <Save className="w-4 h-4" />
                  Save Changes
                </button>
              </div>
            </motion.div>
          </div>
        </>
      )}
    </AnimatePresence>
  );
}
