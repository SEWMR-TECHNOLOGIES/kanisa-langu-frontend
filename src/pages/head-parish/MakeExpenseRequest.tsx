import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Plus, Trash2, Eye, Send, X } from "lucide-react";
import ModernSelect from "../../components/head-parish/ModernSelect";
import ModernDatePicker from "../../components/head-parish/ModernDatePicker";
import NumberInput from "../../components/head-parish/NumberInput";

const expenseGroupOptions = [
  { value: "1", label: "Office & Administration" },
  { value: "2", label: "Church Maintenance" },
  { value: "3", label: "Salaries & Allowances" },
  { value: "4", label: "Utilities" },
  { value: "5", label: "Mission & Evangelism" },
];

const expenseNameOptions: Record<string, { value: string; label: string }[]> = {
  "1": [{ value: "1", label: "Office Supplies" }, { value: "2", label: "Printing" }, { value: "3", label: "Stationery" }],
  "2": [{ value: "4", label: "Repairs" }, { value: "5", label: "Cleaning" }, { value: "6", label: "Painting" }],
  "3": [{ value: "7", label: "Pastor Salary" }, { value: "8", label: "Evangelist Allowance" }],
  "4": [{ value: "9", label: "Electricity" }, { value: "10", label: "Water Bill" }, { value: "11", label: "Internet" }],
  "5": [{ value: "12", label: "Mission Trip" }, { value: "13", label: "Evangelism Materials" }],
};

const subParishOptions = [
  { value: "1", label: "Moshi Mjini" }, { value: "2", label: "Moshi Vijijini" },
  { value: "3", label: "Hai" }, { value: "4", label: "Rombo" },
];

const communityOptions = [
  { value: "1", label: "Mwika" }, { value: "2", label: "Marangu" },
  { value: "3", label: "Machame" }, { value: "4", label: "Kibosho" },
];

const groupOptions = [
  { value: "1", label: "Vijana" }, { value: "2", label: "Wazee" },
  { value: "3", label: "Wanawake" }, { value: "4", label: "Kwaya Kuu" },
];

interface ExpenseItem {
  id: number;
  expense_group: string;
  expense_name: string;
  amount: string;
  description: string;
  request_date: string;
}

const tabConfigs = [
  { id: "head-parish", label: "Head Parish", extraFields: [] as { key: string; label: string; options: { value: string; label: string }[] }[] },
  { id: "sub-parish", label: "Sub Parish", extraFields: [{ key: "sub_parish_id", label: "Sub Parish", options: subParishOptions }] },
  { id: "community", label: "Community", extraFields: [{ key: "sub_parish_id", label: "Sub Parish", options: subParishOptions }, { key: "community_id", label: "Community", options: communityOptions }] },
  { id: "group", label: "Group", extraFields: [{ key: "group_id", label: "Group", options: groupOptions }] },
];

export default function MakeExpenseRequest() {
  const [activeTab, setActiveTab] = useState("head-parish");
  const [containers, setContainers] = useState<Record<string, ExpenseItem[]>>({
    "head-parish": [], "sub-parish": [], "community": [], "group": [],
  });
  const [formState, setFormState] = useState<Record<string, Record<string, string>>>({});
  const [showModal, setShowModal] = useState(false);
  const [modalTab, setModalTab] = useState("");
  const [submitDescription, setSubmitDescription] = useState("");
  let counter = 0;
  Object.values(containers).forEach(items => { counter += items.length; });

  const getForm = (tabId: string) => formState[tabId] || {};
  const updateForm = (tabId: string, key: string, value: string) => {
    setFormState(prev => ({ ...prev, [tabId]: { ...(prev[tabId] || {}), [key]: value } }));
  };

  const addToContainer = (tabId: string) => {
    const form = getForm(tabId);
    if (!form.expense_group_id || !form.expense_name_id || !form.budgeted_amount) return;
    const groupLabel = expenseGroupOptions.find(o => o.value === form.expense_group_id)?.label || "";
    const names = expenseNameOptions[form.expense_group_id || ""] || [];
    const nameLabel = names.find(o => o.value === form.expense_name_id)?.label || "";
    const newItem: ExpenseItem = {
      id: Date.now(),
      expense_group: groupLabel,
      expense_name: nameLabel,
      amount: form.budgeted_amount,
      description: form.budget_description || "",
      request_date: form.request_date || new Date().toISOString().split("T")[0],
    };
    setContainers(prev => ({ ...prev, [tabId]: [...(prev[tabId] || []), newItem] }));
    setFormState(prev => ({
      ...prev,
      [tabId]: { ...(prev[tabId] || {}), budgeted_amount: "", budget_description: "" },
    }));
  };

  const removeItem = (tabId: string, id: number) => {
    setContainers(prev => ({ ...prev, [tabId]: (prev[tabId] || []).filter(i => i.id !== id) }));
  };

  const showContainerModal = (tabId: string) => {
    setModalTab(tabId);
    setShowModal(true);
    setSubmitDescription("");
  };

  const getTotal = (tabId: string) => (containers[tabId] || []).reduce((s, i) => s + (Number(i.amount) || 0), 0);

  const currentTabConfig = tabConfigs.find(t => t.id === activeTab) || tabConfigs[0];
  const currentForm = getForm(activeTab);
  const currentItems = containers[activeTab] || [];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Make Expense Request</h1>
        <p className="text-sm text-admin-text mt-1">Add expense items to the container per level, then submit</p>
      </div>

      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        className="admin-card rounded-2xl overflow-hidden"
      >
        {/* Tab Bar */}
        <div className="px-6 pt-6 border-b border-admin-border/30">
          <div className="flex gap-1 overflow-x-auto pb-0 scrollbar-none">
            {tabConfigs.map((tab) => (
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
                {containers[tab.id].length > 0 && (
                  <span className="absolute -top-1 -right-1 min-w-[18px] h-[18px] rounded-full bg-destructive text-white text-[10px] font-bold flex items-center justify-center px-1">
                    {containers[tab.id].length}
                  </span>
                )}
              </button>
            ))}
          </div>
        </div>

        {/* Form */}
        <motion.div
          key={activeTab}
          initial={{ opacity: 0, x: 8 }}
          animate={{ opacity: 1, x: 0 }}
          className="p-6 lg:p-8"
        >
          <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            {/* Extra fields per tab */}
            {currentTabConfig.extraFields.map((ef) => (
              <div key={ef.key}>
                <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">{ef.label}</label>
                <ModernSelect
                  options={ef.options}
                  value={currentForm[ef.key]}
                  onChange={(v) => updateForm(activeTab, ef.key, v)}
                  placeholder={`Select ${ef.label}`}
                />
              </div>
            ))}

            {/* Common fields */}
            <div>
              <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Expense Group</label>
              <ModernSelect
                options={expenseGroupOptions}
                value={currentForm.expense_group_id}
                onChange={(v) => {
                  updateForm(activeTab, "expense_group_id", v);
                  updateForm(activeTab, "expense_name_id", "");
                }}
                placeholder="Select Expense Group"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Expense Name</label>
              <ModernSelect
                options={expenseNameOptions[currentForm.expense_group_id] || []}
                value={currentForm.expense_name_id}
                onChange={(v) => updateForm(activeTab, "expense_name_id", v)}
                placeholder="Select Expense Name"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
                Request Amount <span className="text-admin-accent">*</span>
              </label>
              <NumberInput
                value={currentForm.budgeted_amount}
                onChange={(v) => updateForm(activeTab, "budgeted_amount", v)}
                placeholder="Request Amount"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
                Request Description <span className="text-admin-accent">*</span>
              </label>
              <input
                type="text"
                value={currentForm.budget_description || ""}
                onChange={(e) => updateForm(activeTab, "budget_description", e.target.value)}
                placeholder="Request Description"
                className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all"
              />
            </div>
            <div>
              <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Request Date</label>
              <ModernDatePicker
                value={currentForm.request_date}
                onChange={(v) => updateForm(activeTab, "request_date", v)}
                placeholder="Pick date"
              />
            </div>
          </div>

          <div className="flex flex-wrap gap-3 pt-6">
            <button
              type="button"
              onClick={() => addToContainer(activeTab)}
              disabled={!currentForm.expense_group_id || !currentForm.expense_name_id || !currentForm.budgeted_amount}
              className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <Plus className="w-4 h-4" />
              Add to Container
            </button>
            <button
              type="button"
              onClick={() => showContainerModal(activeTab)}
              disabled={currentItems.length === 0}
              className="px-6 py-2.5 rounded-xl bg-admin-info/10 text-admin-info font-medium text-sm hover:bg-admin-info/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <Eye className="w-4 h-4" />
              Show {currentTabConfig.label} Items ({currentItems.length})
            </button>
          </div>
        </motion.div>
      </motion.div>

      {/* Container Modal */}
      <AnimatePresence>
        {showModal && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"
              onClick={() => setShowModal(false)}
            />
            <div className="fixed inset-0 z-[61] flex items-center justify-center p-4">
              <motion.div
                initial={{ opacity: 0, scale: 0.95, y: 20 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                exit={{ opacity: 0, scale: 0.95, y: 20 }}
                className="admin-card rounded-2xl w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col"
              >
                <div className="flex items-center justify-between px-6 py-4 border-b border-admin-border/30">
                  <div>
                    <h2 className="text-base font-bold text-admin-text-bright">
                      Expense Request Items — {tabConfigs.find(t => t.id === modalTab)?.label}
                    </h2>
                    <p className="text-xs text-admin-text mt-0.5">{containers[modalTab]?.length || 0} items • Total: TZS {getTotal(modalTab).toLocaleString()}</p>
                  </div>
                  <button onClick={() => setShowModal(false)} className="p-2 rounded-xl hover:bg-admin-surface-hover text-admin-text transition-colors">
                    <X className="w-4 h-4" />
                  </button>
                </div>

                <div className="flex-1 overflow-y-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b border-admin-border/30">
                        <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">#</th>
                        <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Group</th>
                        <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Name</th>
                        <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Amount</th>
                        <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Date</th>
                        <th className="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Remove</th>
                      </tr>
                    </thead>
                    <tbody>
                      {(containers[modalTab] || []).map((item, idx) => (
                        <tr key={item.id} className="border-b border-admin-border/20 hover:bg-admin-surface-hover/50">
                          <td className="px-6 py-3.5 text-sm text-admin-text/60">{idx + 1}</td>
                          <td className="px-6 py-3.5 text-sm text-admin-text-bright">{item.expense_group}</td>
                          <td className="px-6 py-3.5 text-sm text-admin-text-bright">{item.expense_name}</td>
                          <td className="px-6 py-3.5 text-sm text-admin-accent font-medium tabular-nums">TZS {Number(item.amount).toLocaleString()}</td>
                          <td className="px-6 py-3.5 text-sm text-admin-text">{item.request_date}</td>
                          <td className="px-6 py-3.5 text-right">
                            <button onClick={() => removeItem(modalTab, item.id)} className="p-2 rounded-lg hover:bg-destructive/10 text-destructive transition-colors">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* Submit description + button */}
                <div className="px-6 py-4 border-t border-admin-border/30 space-y-3">
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
                      Description of Expenses <span className="text-admin-accent">*</span>
                    </label>
                    <textarea
                      value={submitDescription}
                      onChange={(e) => setSubmitDescription(e.target.value)}
                      placeholder="Enter description of the expense..."
                      rows={2}
                      className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all resize-none"
                    />
                  </div>
                  <div className="flex items-center justify-end gap-3">
                    <button onClick={() => setShowModal(false)} className="px-5 py-2.5 rounded-xl text-sm font-medium text-admin-text hover:bg-admin-surface-hover transition-colors">
                      Close
                    </button>
                    <button
                      disabled={!submitDescription.trim()}
                      className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                      <Send className="w-4 h-4" />
                      Submit Expense Request
                    </button>
                  </div>
                </div>
              </motion.div>
            </div>
          </>
        )}
      </AnimatePresence>
    </div>
  );
}
