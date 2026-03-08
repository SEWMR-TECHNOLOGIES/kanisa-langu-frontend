import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { X, Check, XCircle } from "lucide-react";
import TabbedDataTable from "../../components/head-parish/TabbedDataTable";
import NumberInput from "../../components/head-parish/NumberInput";

const mockExpenseData = {
  "head-parish": Array.from({ length: 8 }, (_, i) => ({
    id: i + 1,
    expense_group: ["Office & Admin", "Maintenance", "Utilities", "Salaries"][i % 4],
    expense_name: ["Office Supplies", "Repairs", "Electricity", "Pastor Salary", "Printing", "Water", "Internet", "Cleaning"][i],
    amount: [150000, 300000, 250000, 1200000, 80000, 120000, 95000, 180000][i],
    request_date: `2025-0${1 + (i % 3)}-${String(5 + i * 3).padStart(2, "0")}`,
    chairperson: i % 3 === 0 ? "Approved" : i % 3 === 1 ? "Pending" : "Rejected",
    pastor: i % 4 === 0 ? "Approved" : "Pending",
  })),
  "sub-parish": Array.from({ length: 5 }, (_, i) => ({
    id: i + 1,
    sub_parish: ["Moshi Mjini", "Hai", "Rombo"][i % 3],
    expense_group: ["Office & Admin", "Maintenance"][i % 2],
    expense_name: ["Stationery", "Painting", "Cleaning", "Printing", "Repairs"][i],
    amount: [80000, 200000, 60000, 45000, 350000][i],
    request_date: `2025-02-${String(1 + i * 5).padStart(2, "0")}`,
    chairperson: i % 2 === 0 ? "Approved" : "Pending",
    pastor: "Pending",
  })),
  "community": Array.from({ length: 4 }, (_, i) => ({
    id: i + 1,
    community: ["Mwika", "Marangu", "Machame", "Kibosho"][i],
    expense_group: "Maintenance",
    expense_name: ["Painting", "Repairs", "Cleaning", "Fencing"][i],
    amount: [150000, 200000, 75000, 400000][i],
    request_date: `2025-01-${String(10 + i * 5).padStart(2, "0")}`,
    chairperson: "Pending",
    pastor: "Pending",
  })),
  "group": Array.from({ length: 3 }, (_, i) => ({
    id: i + 1,
    group: ["Vijana", "Wanawake", "Kwaya Kuu"][i],
    expense_group: "Office & Admin",
    expense_name: ["Materials", "Printing", "Transport"][i],
    amount: [50000, 80000, 120000][i],
    request_date: `2025-03-0${i + 1}`,
    chairperson: i === 0 ? "Approved" : "Pending",
    pastor: "Pending",
  })),
};

function ApprovalBadge({ status }: { status: string }) {
  return (
    <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${
      status === "Approved" ? "bg-admin-success/10 text-admin-success" :
      status === "Rejected" ? "bg-destructive/10 text-destructive" :
      "bg-admin-warning/10 text-admin-warning"
    }`}>{status}</span>
  );
}

export default function ExpenseRequests() {
  const [respondModal, setRespondModal] = useState<{ open: boolean; row: any; tab: string }>({ open: false, row: null, tab: "" });
  const [approval, setApproval] = useState<"approve" | "reject">("approve");
  const [approvedAmount, setApprovedAmount] = useState("");
  const [rejectionReason, setRejectionReason] = useState("");

  const openRespond = (row: any, tab: string) => {
    setRespondModal({ open: true, row, tab });
    setApproval("approve");
    setApprovedAmount(String(row.amount || ""));
    setRejectionReason("");
  };

  return (
    <div className="space-y-6">
      <TabbedDataTable
        title="Expense Requests"
        description="View, approve, or reject expense requests across levels"
        searchPlaceholder="Search expense requests..."
        actions={["view", "edit"]}
        onAction={(action: string, row: any, tab: string) => {
          if (action === "edit") openRespond(row, tab);
        }}
        tabs={[
          {
            id: "head-parish",
            label: "Head Parish",
            columns: [
              { key: "expense_group", label: "Expense Group" },
              { key: "expense_name", label: "Expense Name" },
              { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.amount).toLocaleString()}</span> },
              { key: "request_date", label: "Request Date" },
              { key: "chairperson", label: "Chairperson", render: (r: any) => <ApprovalBadge status={r.chairperson} /> },
              { key: "pastor", label: "Pastor", render: (r: any) => <ApprovalBadge status={r.pastor} /> },
            ],
            data: mockExpenseData["head-parish"],
            searchKeys: ["expense_group", "expense_name"],
          },
          {
            id: "sub-parish",
            label: "Sub Parish",
            columns: [
              { key: "sub_parish", label: "Sub Parish" },
              { key: "expense_group", label: "Expense Group" },
              { key: "expense_name", label: "Expense Name" },
              { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.amount).toLocaleString()}</span> },
              { key: "request_date", label: "Request Date" },
              { key: "chairperson", label: "Chairperson", render: (r: any) => <ApprovalBadge status={r.chairperson} /> },
              { key: "pastor", label: "Pastor", render: (r: any) => <ApprovalBadge status={r.pastor} /> },
            ],
            data: mockExpenseData["sub-parish"],
            searchKeys: ["sub_parish", "expense_name"],
          },
          {
            id: "community",
            label: "Community",
            columns: [
              { key: "community", label: "Community" },
              { key: "expense_group", label: "Expense Group" },
              { key: "expense_name", label: "Expense Name" },
              { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.amount).toLocaleString()}</span> },
              { key: "request_date", label: "Request Date" },
              { key: "chairperson", label: "Chairperson", render: (r: any) => <ApprovalBadge status={r.chairperson} /> },
              { key: "pastor", label: "Pastor", render: (r: any) => <ApprovalBadge status={r.pastor} /> },
            ],
            data: mockExpenseData["community"],
            searchKeys: ["community", "expense_name"],
          },
          {
            id: "group",
            label: "Group",
            columns: [
              { key: "group", label: "Group" },
              { key: "expense_group", label: "Expense Group" },
              { key: "expense_name", label: "Expense Name" },
              { key: "amount", label: "Amount", render: (r: any) => <span className="font-medium text-admin-accent tabular-nums">TZS {Number(r.amount).toLocaleString()}</span> },
              { key: "request_date", label: "Request Date" },
              { key: "chairperson", label: "Chairperson", render: (r: any) => <ApprovalBadge status={r.chairperson} /> },
              { key: "pastor", label: "Pastor", render: (r: any) => <ApprovalBadge status={r.pastor} /> },
            ],
            data: mockExpenseData["group"],
            searchKeys: ["group", "expense_name"],
          },
        ]}
      />

      {/* Respond Modal */}
      <AnimatePresence>
        {respondModal.open && respondModal.row && (
          <>
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]" onClick={() => setRespondModal({ open: false, row: null, tab: "" })} />
            <div className="fixed inset-0 z-[61] flex items-center justify-center p-4">
              <motion.div initial={{ opacity: 0, scale: 0.95, y: 20 }} animate={{ opacity: 1, scale: 1, y: 0 }} exit={{ opacity: 0, scale: 0.95, y: 20 }} className="admin-card rounded-2xl w-full max-w-md overflow-hidden">
                <div className="flex items-center justify-between px-6 py-4 border-b border-admin-border/30">
                  <h2 className="text-base font-bold text-admin-text-bright">Respond to Expense Request</h2>
                  <button onClick={() => setRespondModal({ open: false, row: null, tab: "" })} className="p-2 rounded-xl hover:bg-admin-surface-hover text-admin-text"><X className="w-4 h-4" /></button>
                </div>
                <div className="p-6 space-y-5">
                  <div>
                    <label className="block text-xs font-medium text-admin-text mb-3 uppercase tracking-wider">Response</label>
                    <div className="flex gap-3">
                      <button
                        onClick={() => setApproval("approve")}
                        className={`flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-medium transition-all ${
                          approval === "approve" ? "bg-admin-success/10 text-admin-success border-2 border-admin-success/30" : "bg-admin-bg border border-admin-border text-admin-text hover:bg-admin-surface-hover"
                        }`}
                      >
                        <Check className="w-4 h-4" /> Approve
                      </button>
                      <button
                        onClick={() => setApproval("reject")}
                        className={`flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-medium transition-all ${
                          approval === "reject" ? "bg-destructive/10 text-destructive border-2 border-destructive/30" : "bg-admin-bg border border-admin-border text-admin-text hover:bg-admin-surface-hover"
                        }`}
                      >
                        <XCircle className="w-4 h-4" /> Reject
                      </button>
                    </div>
                  </div>

                  {approval === "approve" && (
                    <div>
                      <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Approved Amount (TZS)</label>
                      <NumberInput value={approvedAmount} onChange={setApprovedAmount} placeholder="Enter approved amount" />
                    </div>
                  )}

                  {approval === "reject" && (
                    <div>
                      <label className="block text-xs font-medium text-admin-text mb-2 uppercase tracking-wider">Rejection Reason</label>
                      <textarea
                        value={rejectionReason}
                        onChange={(e) => setRejectionReason(e.target.value)}
                        rows={3}
                        placeholder="Enter reason for rejection"
                        className="admin-input w-full rounded-xl px-4 py-3 text-sm outline-none transition-all resize-none"
                      />
                    </div>
                  )}
                </div>
                <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-admin-border/30">
                  <button onClick={() => setRespondModal({ open: false, row: null, tab: "" })} className="px-5 py-2.5 rounded-xl text-sm font-medium text-admin-text hover:bg-admin-surface-hover">Cancel</button>
                  <button className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-admin-accent to-amber-600 text-admin-bg font-semibold text-sm hover:opacity-90 transition-opacity admin-glow-gold">
                    Submit Response
                  </button>
                </div>
              </motion.div>
            </div>
          </>
        )}
      </AnimatePresence>
    </div>
  );
}
