import { useState } from "react";
import { motion } from "framer-motion";
import { Landmark } from "lucide-react";
import ModernSelect from "../../components/head-parish/ModernSelect";
import ModernDatePicker from "../../components/head-parish/ModernDatePicker";
import NumberInput from "../../components/head-parish/NumberInput";
import { mockSubParishes } from "../../data/headParishMockData";

const subParishOptions = mockSubParishes.map(s => ({ value: String(s.id), label: s.name || "" }));
const groupOptions = [
  { value: "1", label: "Vijana" }, { value: "2", label: "Wazee" },
  { value: "3", label: "Wanawake" }, { value: "4", label: "Kwaya Kuu" },
];
const revenueStreamOptions = [
  { value: "1", label: "Sadaka ya Ibada" }, { value: "2", label: "Zaka" },
  { value: "3", label: "Sadaka Maalum" }, { value: "4", label: "Ada ya Uanachama" },
];
const serviceNumberOptions = [
  { value: "1", label: "Service 1" }, { value: "2", label: "Service 2" }, { value: "3", label: "Service 3" },
];
const paymentMethodOptions = [
  { value: "Cash", label: "Cash" }, { value: "Bank Transfer", label: "Bank Transfer" },
  { value: "Mobile Payment", label: "Mobile Payment" }, { value: "Card", label: "Card" },
];
const communityOptions = [
  { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" },
  { value: "3", label: "Machame" }, { value: "4", label: "Kibosho" },
];

// Mock unposted counts per tab
const mockUnposted: Record<string, number> = {
  "head-parish": 5,
  "sub-parish": 3,
  "community": 0,
  "group": 2,
  "other": 1,
};

interface FieldDef {
  name: string;
  label: string;
  type: "select" | "number" | "date" | "textarea";
  placeholder?: string;
  required?: boolean;
  options?: { value: string; label: string }[];
  colSpan?: 1 | 2;
}

interface TabDef {
  id: string;
  label: string;
  fields: FieldDef[];
  postLabel: string;
}

const tabs: TabDef[] = [
  {
    id: "head-parish", label: "Head Parish", postLabel: "Post Head Parish Revenues to Bank",
    fields: [
      { name: "service_number", label: "Service Number", type: "select", options: serviceNumberOptions },
      { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "sub_parish_id", label: "Sub Parish", type: "select", options: subParishOptions },
      { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
      { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
      { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Enter description...", colSpan: 2 },
    ],
  },
  {
    id: "sub-parish", label: "Sub Parish", postLabel: "Post Sub Parish Revenues to Bank",
    fields: [
      { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
      { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
      { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
    ],
  },
  {
    id: "community", label: "Community", postLabel: "Post Community Revenues to Bank",
    fields: [
      { name: "sub_parish_id", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "community_id", label: "Community", type: "select", required: true, options: communityOptions },
      { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
      { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
      { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Enter description...", colSpan: 2 },
    ],
  },
  {
    id: "group", label: "Group", postLabel: "Post Groups Revenues to Bank",
    fields: [
      { name: "group_id", label: "Group", type: "select", required: true, options: groupOptions },
      { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
      { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
      { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Enter description..." },
    ],
  },
  {
    id: "other", label: "Other HP Revenues", postLabel: "Post Other Head Parish Revenues to Bank",
    fields: [
      { name: "service_number", label: "Service Number", type: "select", options: serviceNumberOptions },
      { name: "revenue_stream_id", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "revenue_amount", label: "Amount", type: "number", placeholder: "Amount", required: true },
      { name: "payment_method", label: "Payment Method", type: "select", required: true, options: paymentMethodOptions },
      { name: "revenue_date", label: "Revenue Date", type: "date", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Enter description...", colSpan: 2 },
    ],
  },
];

export default function RecordRevenue() {
  const [activeTab, setActiveTab] = useState(tabs[0].id);
  const [formValues, setFormValues] = useState<Record<string, Record<string, string>>>({});

  const updateValue = (tabId: string, name: string, value: string) => {
    setFormValues(prev => ({ ...prev, [tabId]: { ...(prev[tabId] || {}), [name]: value } }));
  };

  const currentTab = tabs.find(t => t.id === activeTab) ?? tabs[0];
  const currentForm = formValues[activeTab] || {};

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Record Revenue</h1>
        <p className="text-sm text-admin-text mt-1">Record revenue at different management levels</p>
      </div>

      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.3 }}
        className="admin-card rounded-2xl overflow-hidden"
      >
        {/* Info box */}
        <div className="mx-6 mt-6 p-4 rounded-xl bg-admin-info/5 border border-admin-info/20">
          <p className="text-sm text-admin-info">
            To post all recorded revenues to the bank, just click the <strong>Post to Bank</strong> button below the form. No need to select any fields or make changes in the form.
          </p>
        </div>

        {/* Tab bar */}
        <div className="px-6 pt-6 border-b border-admin-border/30">
          <div className="flex gap-1 overflow-x-auto pt-2 pb-0 scrollbar-none">
            {tabs.map((tab) => {
              const unposted = mockUnposted[tab.id] || 0;
              return (
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
                  {unposted > 0 && (
                    <span
                      className="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-destructive text-white text-[10px] font-bold flex items-center justify-center px-1"
                      title={`${unposted} unposted revenues`}
                    >
                      {unposted}
                    </span>
                  )}
                </button>
              );
            })}
          </div>
        </div>

        {/* Tab content */}
        <motion.div
          key={currentTab.id}
          initial={{ opacity: 0, x: 8 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.2 }}
          className="p-6 lg:p-8"
        >
          <form
            onSubmit={(e) => {
              e.preventDefault();
              // submit logic
            }}
            className="space-y-6"
          >
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
                      value={currentForm[field.name]}
                      onChange={(val) => updateValue(activeTab, field.name, val)}
                      placeholder={`Select ${field.label}`}
                    />
                  ) : field.type === "date" ? (
                    <ModernDatePicker
                      name={field.name}
                      value={currentForm[field.name]}
                      onChange={(val) => updateValue(activeTab, field.name, val)}
                      placeholder={`Pick ${field.label.toLowerCase()}`}
                    />
                  ) : field.type === "number" ? (
                    <NumberInput
                      name={field.name}
                      value={currentForm[field.name]}
                      onChange={(val) => updateValue(activeTab, field.name, val)}
                      placeholder={field.placeholder}
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
                  ) : null}
                </div>
              ))}
            </div>

            <div className="flex flex-wrap gap-3 pt-2">
              <button
                type="submit"
                className="px-8 py-3 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold"
              >
                Record Revenue
              </button>
              <button
                type="button"
                className="px-6 py-3 rounded-xl bg-admin-success/10 text-admin-success font-semibold text-sm hover:bg-admin-success/20 transition-colors flex items-center gap-2"
              >
                <Landmark className="w-4 h-4" />
                {currentTab.postLabel}
              </button>
            </div>
          </form>
        </motion.div>
      </motion.div>
    </div>
  );
}
