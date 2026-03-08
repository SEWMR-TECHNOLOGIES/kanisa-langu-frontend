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

interface FormCardProps {
  title: string;
  description?: string;
  fields: FormField[];
  submitLabel?: string;
  onSubmit?: (data: Record<string, string>) => void;
}

export default function FormCard({ title, description, fields, submitLabel = "Submit", onSubmit }: FormCardProps) {
  const [formValues, setFormValues] = useState<Record<string, string>>({});

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const data: Record<string, string> = {};
    formData.forEach((value, key) => { data[key] = String(value); });
    // Merge controlled values
    Object.assign(data, formValues);
    onSubmit?.(data);
  };

  const updateValue = (name: string, value: string) => {
    setFormValues(prev => ({ ...prev, [name]: value }));
  };

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
        className="admin-card rounded-2xl p-6 lg:p-8"
      >
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            {fields.map((field) => (
              <div key={field.name} className={field.colSpan === 2 ? "md:col-span-2" : ""}>
                <label htmlFor={field.name} className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
                  {field.label} {field.required && <span className="text-admin-accent">*</span>}
                </label>
                {field.type === "select" ? (
                  <ModernSelect
                    name={field.name}
                    options={field.options || []}
                    value={formValues[field.name]}
                    onChange={(val) => updateValue(field.name, val)}
                    placeholder={field.placeholder || `Select ${field.label}`}
                    required={field.required}
                  />
                ) : field.type === "date" ? (
                  <ModernDatePicker
                    name={field.name}
                    value={formValues[field.name]}
                    onChange={(val) => updateValue(field.name, val)}
                    placeholder={field.placeholder || `Pick ${field.label.toLowerCase()}`}
                    required={field.required}
                  />
                ) : field.type === "file" ? (
                  <ModernFileUpload
                    name={field.name}
                    accept={field.accept || ".xls,.xlsx"}
                    required={field.required}
                  />
                ) : field.type === "textarea" ? (
                  <textarea
                    id={field.name}
                    name={field.name}
                    required={field.required}
                    placeholder={field.placeholder}
                    rows={4}
                    className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all resize-none"
                  />
                ) : (
                  <input
                    id={field.name}
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
              {submitLabel}
            </button>
          </div>
        </form>
      </motion.div>
    </div>
  );
}
