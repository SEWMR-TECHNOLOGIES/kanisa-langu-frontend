import { useState } from "react";
import { motion } from "framer-motion";
import ModernSelect from "./ModernSelect";
import ModernDatePicker from "./ModernDatePicker";
import ModernFileUpload from "./ModernFileUpload";

interface FormField {
  name: string;
  label: string;
  type: "text" | "email" | "tel" | "date" | "select" | "textarea" | "number" | "file" | "time";
  placeholder?: string;
  required?: boolean;
  readOnly?: boolean;
  options?: { value: string; label: string }[];
  colSpan?: 1 | 2;
  accept?: string;
}

interface TabConfig {
  id: string;
  label: string;
  fields: FormField[];
  submitLabel?: string;
  badge?: string;
}

interface TabbedFormCardProps {
  title: string;
  description?: string;
  tabs: TabConfig[];
  onSubmit?: (tabId: string, data: Record<string, string>) => void;
  infoBox?: string;
}

export default function TabbedFormCard({ title, description, tabs, onSubmit, infoBox }: TabbedFormCardProps) {
  const [activeTab, setActiveTab] = useState(tabs[0]?.id || "");
  const [formValues, setFormValues] = useState<Record<string, Record<string, string>>>({});

  const updateValue = (tabId: string, name: string, value: string) => {
    setFormValues(prev => ({
      ...prev,
      [tabId]: { ...(prev[tabId] || {}), [name]: value },
    }));
  };

  const handleSubmit = (tabId: string) => (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const data: Record<string, string> = {};
    formData.forEach((value, key) => { data[key] = String(value); });
    Object.assign(data, formValues[tabId] || {});
    onSubmit?.(tabId, data);
  };

  const currentTab = tabs.find(t => t.id === activeTab);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">{title}</h1>
        {description && <p className="text-sm text-admin-text mt-1">{description}</p>}
      </div>

      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.3 }}
        className="admin-card rounded-2xl overflow-hidden"
      >
        {/* Info box */}
        {infoBox && (
          <div className="mx-6 mt-6 p-4 rounded-xl bg-admin-info/5 border border-admin-info/20">
            <p className="text-sm text-admin-info" dangerouslySetInnerHTML={{ __html: infoBox }} />
          </div>
        )}

        {/* Tab bar */}
        <div className="px-6 pt-6 border-b border-admin-border/30">
          <div className="flex gap-1 overflow-x-auto pb-0 scrollbar-none">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                type="button"
                onClick={() => setActiveTab(tab.id)}
                className={`relative px-4 py-2.5 text-sm font-medium whitespace-nowrap rounded-t-xl transition-all duration-200 ${
                  activeTab === tab.id
                    ? "text-admin-accent bg-admin-accent/5 border-b-2 border-admin-accent"
                    : "text-admin-text hover:text-admin-text-bright hover:bg-admin-surface-hover"
                }`}
              >
                {tab.label}
                {tab.badge && (
                  <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-destructive text-white text-[10px] font-bold flex items-center justify-center px-1">
                    {tab.badge}
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>

        {/* Tab content */}
        {currentTab && (
          <motion.div
            key={currentTab.id}
            initial={{ opacity: 0, x: 8 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.2 }}
            className="p-6 lg:p-8"
          >
            <form onSubmit={handleSubmit(currentTab.id)} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                {currentTab.fields.map((field) => (
                  <div key={field.name} className={field.colSpan === 2 ? "md:col-span-2" : ""}>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
                      {field.label} {field.required && <span className="text-admin-accent">*</span>}
                    </label>
                    {field.type === "select" ? (
                      <ModernSelect
                        name={field.name}
                        options={field.options || []}
                        value={(formValues[currentTab.id] || {})[field.name]}
                        onChange={(val) => updateValue(currentTab.id, field.name, val)}
                        placeholder={field.placeholder || `Select ${field.label}`}
                      />
                    ) : field.type === "date" ? (
                      <ModernDatePicker
                        name={field.name}
                        value={(formValues[currentTab.id] || {})[field.name]}
                        onChange={(val) => updateValue(currentTab.id, field.name, val)}
                        placeholder={field.placeholder || `Pick ${field.label.toLowerCase()}`}
                      />
                    ) : field.type === "file" ? (
                      <ModernFileUpload
                        name={field.name}
                        accept={field.accept || ".xls,.xlsx"}
                        required={field.required}
                      />
                    ) : field.type === "textarea" ? (
                      <textarea
                        name={field.name}
                        required={field.required}
                        placeholder={field.placeholder}
                        rows={3}
                        className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all resize-none"
                      />
                    ) : (
                      <input
                        name={field.name}
                        type={field.type}
                        required={field.required}
                        readOnly={field.readOnly}
                        placeholder={field.placeholder}
                        className={`admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all ${field.readOnly ? "opacity-60 cursor-not-allowed" : ""}`}
                      />
                    )}
                  </div>
                ))}
              </div>

              <div className="pt-2">
                <button
                  type="submit"
                  className="px-8 py-3 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold"
                >
                  {currentTab.submitLabel || "Submit"}
                </button>
              </div>
            </form>
          </motion.div>
        )}
      </motion.div>
    </div>
  );
}
