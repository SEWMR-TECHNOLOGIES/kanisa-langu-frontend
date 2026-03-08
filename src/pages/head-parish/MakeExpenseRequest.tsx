import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Plus, Trash2, Send } from "lucide-react";
import ModernSelect from "../../components/head-parish/ModernSelect";
import NumberInput from "../../components/head-parish/NumberInput";

const expenseOptions = [
  { value: "1", label: "Office Supplies" }, { value: "2", label: "Fuel" },
  { value: "3", label: "Printing" }, { value: "4", label: "Electricity" },
  { value: "5", label: "Water Bill" }, { value: "6", label: "Internet" },
  { value: "7", label: "Church Maintenance" }, { value: "8", label: "Salaries" },
];

interface ExpenseItem {
  id: number;
  expense_name: string;
  amount: string;
  description: string;
}

export default function MakeExpenseRequest() {
  const [items, setItems] = useState<ExpenseItem[]>([]);
  const [currentExpense, setCurrentExpense] = useState("");
  const [currentAmount, setCurrentAmount] = useState("");
  const [currentDesc, setCurrentDesc] = useState("");
  let counter = items.length;

  const addItem = () => {
    if (!currentExpense || !currentAmount) return;
    const expenseLabel = expenseOptions.find(o => o.value === currentExpense)?.label || "";
    setItems(prev => [...prev, {
      id: ++counter,
      expense_name: expenseLabel,
      amount: currentAmount,
      description: currentDesc,
    }]);
    setCurrentExpense("");
    setCurrentAmount("");
    setCurrentDesc("");
  };

  const removeItem = (id: number) => {
    setItems(prev => prev.filter(i => i.id !== id));
  };

  const total = items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-bold text-admin-text-bright font-display">Make Expense Request</h1>
        <p className="text-sm text-admin-text mt-1">Add expense items to your request, then submit all at once</p>
      </div>

      {/* Add Item Form */}
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        animate={{ opacity: 1, y: 0 }}
        className="admin-card rounded-2xl p-6 lg:p-8"
      >
        <h2 className="text-sm font-semibold text-admin-text-bright mb-5">Add Expense Item</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <div>
            <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
              Expense Name <span className="text-admin-accent">*</span>
            </label>
            <ModernSelect
              options={expenseOptions}
              value={currentExpense}
              onChange={setCurrentExpense}
              placeholder="Select expense"
            />
          </div>
          <div>
            <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
              Amount (TZS) <span className="text-admin-accent">*</span>
            </label>
            <NumberInput
              value={currentAmount}
              onChange={setCurrentAmount}
              placeholder="Enter amount"
            />
          </div>
          <div className="md:col-span-2">
            <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">
              Description
            </label>
            <textarea
              value={currentDesc}
              onChange={(e) => setCurrentDesc(e.target.value)}
              placeholder="Describe the expense..."
              rows={3}
              className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all resize-none"
            />
          </div>
        </div>
        <div className="pt-4">
          <button
            type="button"
            onClick={addItem}
            disabled={!currentExpense || !currentAmount}
            className="px-6 py-2.5 rounded-xl bg-admin-info/10 text-admin-info font-medium text-sm hover:bg-admin-info/20 transition-colors disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <Plus className="w-4 h-4" />
            Add to Request
          </button>
        </div>
      </motion.div>

      {/* Items Container */}
      {items.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 16 }}
          animate={{ opacity: 1, y: 0 }}
          className="admin-card rounded-2xl overflow-hidden"
        >
          <div className="px-6 py-4 border-b border-admin-border/30 flex items-center justify-between">
            <h2 className="text-sm font-semibold text-admin-text-bright">
              Request Items ({items.length})
            </h2>
            <div className="text-sm font-bold text-admin-accent tabular-nums">
              Total: TZS {total.toLocaleString()}
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-admin-border/30">
                  <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">#</th>
                  <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Expense</th>
                  <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Amount</th>
                  <th className="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Description</th>
                  <th className="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-admin-text/60">Remove</th>
                </tr>
              </thead>
              <tbody>
                <AnimatePresence>
                  {items.map((item, idx) => (
                    <motion.tr
                      key={item.id}
                      initial={{ opacity: 0, x: -10 }}
                      animate={{ opacity: 1, x: 0 }}
                      exit={{ opacity: 0, x: 10 }}
                      className="border-b border-admin-border/20 hover:bg-admin-surface-hover/50 transition-colors"
                    >
                      <td className="px-6 py-3.5 text-sm text-admin-text/60">{idx + 1}</td>
                      <td className="px-6 py-3.5 text-sm text-admin-text-bright font-medium">{item.expense_name}</td>
                      <td className="px-6 py-3.5 text-sm text-admin-accent font-medium tabular-nums">TZS {Number(item.amount).toLocaleString()}</td>
                      <td className="px-6 py-3.5 text-sm text-admin-text">{item.description || "—"}</td>
                      <td className="px-6 py-3.5 text-right">
                        <button
                          onClick={() => removeItem(item.id)}
                          className="p-2 rounded-lg hover:bg-destructive/10 text-destructive transition-colors"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </td>
                    </motion.tr>
                  ))}
                </AnimatePresence>
              </tbody>
            </table>
          </div>

          <div className="px-6 py-4 border-t border-admin-border/30">
            <button className="px-8 py-3 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold flex items-center gap-2">
              <Send className="w-4 h-4" />
              Submit Expense Request ({items.length} items — TZS {total.toLocaleString()})
            </button>
          </div>
        </motion.div>
      )}
    </div>
  );
}
