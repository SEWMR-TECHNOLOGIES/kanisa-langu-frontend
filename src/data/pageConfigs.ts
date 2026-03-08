// @ts-nocheck
// Route configuration for auto-generating pages
import { type StatusItem } from "../components/head-parish/MemberStatusPreview";
import { getHarambeeStatusItems, getEnvelopeStatusItems } from "../components/head-parish/MemberStatusPreview";
// Each route maps to either a "form" or "table" page type with full config

export interface FormFieldConfig {
  name: string;
  label: string;
  type: "text" | "email" | "tel" | "date" | "select" | "textarea" | "number";
  placeholder?: string;
  required?: boolean;
  readOnly?: boolean;
  options?: { value: string; label: string }[];
  colSpan?: 1 | 2;
}

export interface TableColumnConfig {
  key: string;
  label: string;
  type?: "text" | "badge" | "currency" | "progress" | "number";
  badgeColors?: Record<string, string>;
}

export interface PageConfig {
  title: string;
  description: string;
  type: "form" | "table" | "report";
  // Form config
  submitLabel?: string;
  fields?: FormFieldConfig[];
  infoBox?: string;
  statusPreview?: {
    watchFields: string[];
    getStatus: (values: Record<string, string>) => StatusItem[];
  };
  // Table config
  columns?: TableColumnConfig[];
  data?: Record<string, any>[];
  searchKeys?: string[];
  searchPlaceholder?: string;
  actions?: ("view" | "edit" | "delete")[];
  // Report config
  stats?: { title: string; value: string; change?: string; trend?: "up" | "down" }[];
}

// Reusable select options
const subParishOptions = [
  { value: "Moshi Mjini", label: "Moshi Mjini" },
  { value: "Moshi Vijijini", label: "Moshi Vijijini" },
  { value: "Hai", label: "Hai" },
  { value: "Rombo", label: "Rombo" },
];

const communityOptions = [
  { value: "Mwika", label: "Mwika" },
  { value: "Marangu", label: "Marangu" },
  { value: "Machame", label: "Machame" },
  { value: "Kibosho", label: "Kibosho" },
];

const groupOptions = [
  { value: "Vijana", label: "Vijana" },
  { value: "Wazee", label: "Wazee" },
  { value: "Wanawake", label: "Wanawake" },
  { value: "Kwaya Kuu", label: "Kwaya Kuu" },
];

const bankAccountOptions = [
  { value: "1", label: "Main Account - CRDB" },
  { value: "2", label: "Building Fund - NMB" },
  { value: "3", label: "Harambee Account - NBC" },
  { value: "4", label: "Missions Fund - Stanbic" },
];

const revenueStreamOptions = [
  { value: "1", label: "Sadaka ya Ibada" },
  { value: "2", label: "Zaka" },
  { value: "3", label: "Sadaka Maalum" },
  { value: "4", label: "Ada ya Uanachama" },
  { value: "5", label: "Michango ya Harambee" },
];

const harambeeOptions = [
  { value: "1", label: "Church Building" },
  { value: "2", label: "School Renovation" },
  { value: "3", label: "Pastor's House" },
  { value: "4", label: "New Roof" },
];

const harambeeClassOptions = [
  { value: "A", label: "Class A - TZS 500,000+" },
  { value: "B", label: "Class B - TZS 200,000-499,999" },
  { value: "C", label: "Class C - TZS 100,000-199,999" },
  { value: "D", label: "Class D - Below TZS 100,000" },
];

const memberOptions = Array.from({ length: 10 }, (_, i) => ({
  value: String(i + 1),
  label: `${["Juma", "Maria", "Peter", "Grace", "John", "Anna", "David", "Sarah", "James", "Ruth"][i]} ${["Mwangi", "Kimaro", "Mushi", "Urassa", "Massawe", "Lyimo", "Shirima", "Pallangyo", "Maro", "Swai"][i]}`,
}));

const exclusionReasonOptions = [
  { value: "relocated", label: "Relocated" },
  { value: "deceased", label: "Deceased" },
  { value: "transferred", label: "Transferred" },
  { value: "inactive", label: "Inactive" },
  { value: "personal", label: "Personal Request" },
];

const expenseGroupOptions = [
  { value: "1", label: "Office & Administration" },
  { value: "2", label: "Church Maintenance" },
  { value: "3", label: "Salaries & Allowances" },
  { value: "4", label: "Utilities" },
  { value: "5", label: "Mission & Evangelism" },
];

const assetOptions = [
  { value: "1", label: "Church Building" },
  { value: "2", label: "Parish House" },
  { value: "3", label: "School Block" },
  { value: "4", label: "Guest House" },
  { value: "5", label: "Farm Land" },
  { value: "6", label: "Vehicle" },
];

const statusBadge: Record<string, string> = {
  Active: "bg-admin-success/10 text-admin-success",
  Inactive: "bg-admin-text/10 text-admin-text",
  Pending: "bg-admin-warning/10 text-admin-warning",
  Approved: "bg-admin-success/10 text-admin-success",
  Rejected: "bg-destructive/10 text-destructive",
  Completed: "bg-admin-success/10 text-admin-success",
  Distributed: "bg-admin-info/10 text-admin-info",
  "Not Distributed": "bg-admin-warning/10 text-admin-warning",
  Sent: "bg-admin-success/10 text-admin-success",
  "Not Sent": "bg-admin-text/10 text-admin-text",
};

// Helper to generate mock table data
function genData(count: number, generator: (i: number) => Record<string, any>) {
  return Array.from({ length: count }, (_, i) => ({ id: i + 1, ...generator(i) }));
}

// Mock member status data for previews
const mockHarambeeStatus = (values: Record<string, string>): StatusItem[] => {
  const memberId = Number(values.member || values.member_id || 0);
  const targets = [500000, 400000, 300000, 600000, 250000, 350000, 450000, 200000, 550000, 380000];
  const contribs = [320000, 280000, 150000, 600000, 100000, 350000, 200000, 50000, 400000, 190000];
  const idx = (memberId - 1) % 10;
  return getHarambeeStatusItems(targets[idx] || 0, contribs[idx] || 0);
};

const mockEnvelopeStatus = (values: Record<string, string>): StatusItem[] => {
  const memberId = Number(values.member || values.member_id || 0);
  const targets = [120000, 96000, 84000, 150000, 72000, 108000, 60000, 132000, 90000, 100000];
  const contribs = [80000, 96000, 40000, 100000, 72000, 50000, 30000, 132000, 45000, 60000];
  const idx = (memberId - 1) % 10;
  return getEnvelopeStatusItems(targets[idx] || 0, contribs[idx] || 0);
};

const harambeeStatusPreview = {
  watchFields: ["harambee", "member"],
  getStatus: mockHarambeeStatus,
};

const envelopeStatusPreview = {
  watchFields: ["member"],
  getStatus: mockEnvelopeStatus,
};

// ============ HEAD PARISH PAGE CONFIGS ============
export const headParishPages: Record<string, PageConfig> = {
  // Church Members
  "upload-church-members": {
    title: "Upload Church Members",
    description: "Upload members from a CSV or Excel file",
    type: "form",
    submitLabel: "Upload File",
    fields: [
      { name: "sub_parish", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "community", label: "Community", type: "select", required: true, options: communityOptions },
      { name: "file_note", label: "File Description", type: "text", placeholder: "e.g. Members list Q1 2025" },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional notes about this upload", colSpan: 2 },
    ],
  },
  "church-members-accounts": {
    title: "Active Member Accounts",
    description: "Members with active app accounts",
    type: "table",
    columns: [
      { key: "name", label: "Member Name" },
      { key: "phone", label: "Phone" },
      { key: "email", label: "Email" },
      { key: "last_login", label: "Last Login" },
      { key: "status", label: "Status", type: "badge", badgeColors: statusBadge },
    ],
    data: genData(20, i => ({
      name: memberOptions[i % 10].label,
      phone: `07${String(10000000 + i * 111).slice(0, 8)}`,
      email: `member${i + 1}@gmail.com`,
      last_login: i % 3 === 0 ? "Today" : i % 3 === 1 ? "Yesterday" : `${2 + i} days ago`,
      status: i % 5 === 0 ? "Inactive" : "Active",
    })),
    searchKeys: ["name", "email"],
    actions: ["view"],
  },
  "download-church-members-list": {
    title: "Download Members List",
    description: "Generate and download a list of all church members",
    type: "form",
    submitLabel: "Generate & Download",
    fields: [
      { name: "sub_parish", label: "Sub Parish", type: "select", options: [{ value: "all", label: "All Sub Parishes" }, ...subParishOptions] },
      { name: "community", label: "Community", type: "select", options: [{ value: "all", label: "All Communities" }, ...communityOptions] },
      { name: "member_type", label: "Member Type", type: "select", options: [{ value: "all", label: "All Types" }, { value: "mwenyeji", label: "Mwenyeji" }, { value: "mgeni", label: "Mgeni" }] },
      { name: "format", label: "File Format", type: "select", required: true, options: [{ value: "csv", label: "CSV" }, { value: "xlsx", label: "Excel (XLSX)" }, { value: "pdf", label: "PDF" }] },
    ],
  },
  // Member Exclusion
  "add-exclusion-reason": {
    title: "Add Exclusion Reason",
    description: "Define a new reason for member exclusion",
    type: "form",
    submitLabel: "Add Reason",
    fields: [
      { name: "reason", label: "Exclusion Reason", type: "text", placeholder: "Enter reason", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Describe this exclusion reason", colSpan: 2 },
    ],
  },
  "member-exclusions": {
    title: "View Exclusion Reasons",
    description: "All defined member exclusion reasons",
    type: "table",
    columns: [
      { key: "reason", label: "Reason" },
      { key: "description", label: "Description" },
      { key: "members_count", label: "Members Excluded" },
    ],
    data: genData(5, i => ({
      reason: ["Relocated", "Deceased", "Transferred", "Inactive", "Personal Request"][i],
      description: ["Member moved to another parish", "Member passed away", "Transferred to another church", "No activity for 2+ years", "Requested voluntary exclusion"][i],
      members_count: [12, 8, 5, 18, 3][i],
    })),
    searchKeys: ["reason"],
    actions: ["edit", "delete"],
  },
  "exclude-church-member": {
    title: "Exclude Church Member",
    description: "Remove a member from the active parish register",
    type: "form",
    submitLabel: "Exclude Member",
    fields: [
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "reason", label: "Exclusion Reason", type: "select", required: true, options: exclusionReasonOptions },
      { name: "date", label: "Effective Date", type: "date", required: true },
      { name: "notes", label: "Additional Notes", type: "textarea", placeholder: "Any additional notes", colSpan: 2 },
    ],
  },
  // Sunday Services
  "set-services-count": {
    title: "Set Services Count",
    description: "Configure the number of Sunday services",
    type: "form",
    submitLabel: "Save Settings",
    fields: [
      { name: "count", label: "Number of Services", type: "number", placeholder: "e.g. 3", required: true },
      { name: "effective_date", label: "Effective From", type: "date", required: true },
    ],
  },
  "set-service-time": {
    title: "Set Service Times",
    description: "Configure service schedule times",
    type: "form",
    submitLabel: "Save Times",
    fields: [
      { name: "service_number", label: "Service Number", type: "select", required: true, options: [{ value: "1", label: "1st Service" }, { value: "2", label: "2nd Service" }, { value: "3", label: "3rd Service" }] },
      { name: "start_time", label: "Start Time", type: "text", placeholder: "e.g. 07:00 AM", required: true },
      { name: "end_time", label: "End Time", type: "text", placeholder: "e.g. 09:00 AM" },
      { name: "description", label: "Service Name", type: "text", placeholder: "e.g. Morning Service" },
    ],
  },
  "services": {
    title: "Services Numbers",
    description: "Sunday service attendance numbers",
    type: "table",
    columns: [
      { key: "date", label: "Date" },
      { key: "service", label: "Service" },
      { key: "attendance", label: "Attendance", type: "number" },
      { key: "offering", label: "Offering", type: "currency" },
    ],
    data: genData(15, i => ({
      date: `2025-${String(1 + (i % 12)).padStart(2, "0")}-${String(1 + (i * 7) % 28).padStart(2, "0")}`,
      service: `${(i % 3) + 1}${["st", "nd", "rd"][i % 3]} Service`,
      attendance: [280, 320, 150, 310, 295, 180, 340, 350, 160, 290, 330, 170, 315, 300, 190][i],
      offering: `TZS ${[450, 520, 280, 380, 490, 310, 560, 600, 290, 410, 530, 300, 470, 510, 320][i]},000`,
    })),
    searchKeys: ["date"],
    actions: ["view", "edit"],
  },
  // Attendance
  "set-attendance-benchmark": {
    title: "Set Attendance Benchmark",
    description: "Define expected attendance targets",
    type: "form",
    submitLabel: "Save Benchmark",
    fields: [
      { name: "service", label: "Service", type: "select", required: true, options: [{ value: "1", label: "1st Service" }, { value: "2", label: "2nd Service" }, { value: "3", label: "3rd Service" }] },
      { name: "benchmark", label: "Expected Attendance", type: "number", placeholder: "e.g. 300", required: true },
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
    ],
  },
  "record-attendance": {
    title: "Record Attendance",
    description: "Record attendance for a service or event",
    type: "form",
    submitLabel: "Record Attendance",
    fields: [
      { name: "date", label: "Date", type: "date", required: true },
      { name: "service", label: "Service", type: "select", required: true, options: [{ value: "1", label: "1st Service" }, { value: "2", label: "2nd Service" }, { value: "3", label: "3rd Service" }] },
      { name: "men", label: "Men", type: "number", placeholder: "0" },
      { name: "women", label: "Women", type: "number", placeholder: "0" },
      { name: "youth", label: "Youth", type: "number", placeholder: "0" },
      { name: "children", label: "Children", type: "number", placeholder: "0" },
    ],
  },
  // Banking
  "record-parish-transactions": {
    title: "Record Parish Transactions",
    description: "Record a bank transaction",
    type: "form",
    submitLabel: "Record Transaction",
    fields: [
      { name: "account", label: "Bank Account", type: "select", required: true, options: bankAccountOptions },
      { name: "type", label: "Transaction Type", type: "select", required: true, options: [{ value: "deposit", label: "Deposit" }, { value: "withdrawal", label: "Withdrawal" }, { value: "transfer", label: "Transfer" }] },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
      { name: "reference", label: "Reference Number", type: "text", placeholder: "e.g. TXN-001" },
      { name: "description", label: "Description", type: "textarea", placeholder: "Transaction details", colSpan: 2 },
    ],
  },
  // Revenue
  "create-revenue-groups": {
    title: "Create Revenue Groups",
    description: "Define revenue group categories",
    type: "form",
    submitLabel: "Create Group",
    fields: [
      { name: "name", label: "Group Name", type: "text", placeholder: "e.g. Offerings", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Describe this revenue group", colSpan: 2 },
    ],
  },
  "add-revenue-stream": {
    title: "Add Revenue Stream",
    description: "Create a new revenue stream",
    type: "form",
    submitLabel: "Add Stream",
    fields: [
      { name: "name", label: "Stream Name", type: "text", placeholder: "e.g. Sadaka ya Ibada", required: true },
      { name: "group", label: "Revenue Group", type: "select", required: true, options: [{ value: "offerings", label: "Offerings" }, { value: "tithes", label: "Tithes" }, { value: "special", label: "Special Collections" }, { value: "other", label: "Other" }] },
      { name: "account", label: "Bank Account", type: "select", required: true, options: bankAccountOptions },
      { name: "description", label: "Description", type: "textarea", placeholder: "Description", colSpan: 2 },
    ],
  },
  "map-revenue-streams": {
    title: "Map Revenue Streams",
    description: "Map revenue streams to sub parishes",
    type: "form",
    submitLabel: "Map Stream",
    fields: [
      { name: "stream", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "sub_parish", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "target", label: "Annual Target (TZS)", type: "number", placeholder: "Enter target amount" },
    ],
  },
  "link-revenue-stream": {
    title: "Link Revenue Stream",
    description: "Link a revenue stream to a bank account",
    type: "form",
    submitLabel: "Link Stream",
    fields: [
      { name: "stream", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "account", label: "Bank Account", type: "select", required: true, options: bankAccountOptions },
    ],
  },
  "verify-revenues": {
    title: "Verify Revenues",
    description: "Review and verify recorded revenues",
    type: "table",
    columns: [
      { key: "date", label: "Date" },
      { key: "stream", label: "Revenue Stream" },
      { key: "amount", label: "Amount" },
      { key: "recorded_by", label: "Recorded By" },
      { key: "status", label: "Status", type: "badge", badgeColors: statusBadge },
    ],
    data: genData(15, i => ({
      date: `2025-${String(1 + (i % 12)).padStart(2, "0")}-${String(1 + i * 2).padStart(2, "0")}`,
      stream: revenueStreamOptions[i % 5].label,
      amount: `TZS ${(i + 1) * 250},000`,
      recorded_by: memberOptions[i % 10].label,
      status: i % 3 === 0 ? "Pending" : "Approved",
    })),
    searchKeys: ["stream", "recorded_by"],
    actions: ["view", "edit"],
  },
  "envelope-usage": {
    title: "Envelope Usage Report",
    description: "Generate envelope usage report for a specific date",
    type: "form",
    submitLabel: "Generate Report",
    infoBox: "<strong>Note:</strong> The <strong>Benchmark</strong> is the standard measure of how real attendance compares to it. If not set, the default benchmark is 1000.",
    fields: [
      { name: "usage_date", label: "Select Date", type: "date", required: true },
      { name: "benchmark", label: "Benchmark (Optional)", type: "number", placeholder: "Enter Benchmark (default: 1000)" },
    ],
  },
  "set-annual-revenue-target": {
    title: "Set Annual Revenue Target",
    description: "Set the overall annual collection target",
    type: "form",
    submitLabel: "Save Target",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "target", label: "Annual Target (TZS)", type: "number", placeholder: "e.g. 150000000", required: true },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Any notes about this target", colSpan: 2 },
    ],
  },
  "set-revenue-stream-target": {
    title: "Set Revenue Stream Target",
    description: "Set target for a specific revenue stream",
    type: "form",
    submitLabel: "Save Target",
    fields: [
      { name: "stream", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions },
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "target", label: "Target Amount (TZS)", type: "number", placeholder: "Enter target", required: true },
    ],
  },
  "distribute-annual-revenue-target": {
    title: "Distribute Revenue Target",
    description: "Distribute annual targets to sub parishes",
    type: "form",
    submitLabel: "Distribute",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "sub_parish", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "amount", label: "Target Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  // Debits
  "record-debit": {
    title: "Record Debit",
    description: "Record a new debit or loan",
    type: "form",
    submitLabel: "Record Debit",
    fields: [
      { name: "description", label: "Description", type: "text", placeholder: "Enter description", required: true },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date_debited", label: "Date Debited", type: "date", required: true },
      { name: "return_before_date", label: "Return Before Date", type: "date" },
      { name: "purpose", label: "Purpose", type: "textarea", placeholder: "Enter purpose...", colSpan: 2 },
    ],
  },
  // Budgeting
  "ogo": {
    title: "OGO - Budget Overview",
    description: "Overall budget overview and planning",
    type: "table",
    columns: [
      { key: "category", label: "Category" },
      { key: "budgeted", label: "Budgeted" },
      { key: "actual", label: "Actual" },
      { key: "variance", label: "Variance" },
      { key: "status", label: "Status", type: "badge", badgeColors: { "On Track": "bg-admin-success/10 text-admin-success", "Over Budget": "bg-destructive/10 text-destructive", "Under Budget": "bg-admin-info/10 text-admin-info" } },
    ],
    data: genData(8, i => ({
      category: ["Salaries", "Maintenance", "Utilities", "Office", "Mission", "Events", "Construction", "Vehicles"][i],
      budgeted: `TZS ${[15, 8, 3, 2, 5, 4, 20, 6][i]}M`,
      actual: `TZS ${[14.5, 9.2, 2.8, 2.5, 4.2, 3.8, 22, 5.5][i]}M`,
      variance: `${["+3.3%", "-15%", "+6.7%", "-25%", "+16%", "+5%", "-10%", "+8.3%"][i]}`,
      status: ["+3.3%", "+6.7%", "+16%", "+5%", "+8.3%"].includes(["+3.3%", "-15%", "+6.7%", "-25%", "+16%", "+5%", "-10%", "+8.3%"][i]) ? "Under Budget" : i === 7 ? "On Track" : "Over Budget",
    })),
    searchKeys: ["category"],
    actions: ["view", "edit"],
  },
  // Expense Management
  "create-expense-groups": {
    title: "Create Expense Groups",
    description: "Define expense group categories",
    type: "form",
    submitLabel: "Create Group",
    fields: [
      { name: "name", label: "Group Name", type: "text", placeholder: "e.g. Office & Administration", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Describe this expense group", colSpan: 2 },
    ],
  },
  "record-expense-names": {
    title: "Record Expense Names",
    description: "Add expense line items under groups",
    type: "form",
    submitLabel: "Add Expense Name",
    fields: [
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "name", label: "Expense Name", type: "text", placeholder: "e.g. Stationery", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Details" },
    ],
  },
  "set-annual-expense-budget": {
    title: "Set Annual Expense Budget",
    description: "Set the annual budget for expense groups",
    type: "form",
    submitLabel: "Save Budget",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "amount", label: "Budget Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "distribute-annual-expense-budget": {
    title: "Distribute Expense Budget",
    description: "Distribute annual budgets quarterly or monthly",
    type: "form",
    submitLabel: "Distribute Budget",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "period", label: "Distribution Period", type: "select", required: true, options: [{ value: "monthly", label: "Monthly" }, { value: "quarterly", label: "Quarterly" }] },
    ],
  },
  "set-expense-budget": {
    title: "Allocate Expense Budgets",
    description: "Allocate budgets to specific expense items",
    type: "form",
    submitLabel: "Allocate Budget",
    fields: [
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "expense", label: "Expense Name", type: "select", required: true, options: [{ value: "stationery", label: "Stationery" }, { value: "fuel", label: "Fuel" }, { value: "electricity", label: "Electricity" }, { value: "water", label: "Water" }, { value: "internet", label: "Internet" }] },
      { name: "amount", label: "Budget Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "grouped-requests": {
    title: "Grouped Expense Requests",
    description: "View expense requests grouped by category",
    type: "table",
    columns: [
      { key: "group", label: "Expense Group" },
      { key: "total_requests", label: "Requests" },
      { key: "total_amount", label: "Total Amount" },
      { key: "approved", label: "Approved" },
      { key: "pending", label: "Pending" },
    ],
    data: genData(5, i => ({
      group: expenseGroupOptions[i].label,
      total_requests: [8, 5, 3, 12, 4][i],
      total_amount: `TZS ${[2.4, 1.8, 4.5, 1.2, 3.5][i]}M`,
      approved: [5, 3, 2, 8, 2][i],
      pending: [3, 2, 1, 4, 2][i],
    })),
    searchKeys: ["group"],
    actions: ["view"],
  },
  // Assets
  "set-asset-status": {
    title: "Set Asset Status",
    description: "Update the status of a church asset",
    type: "form",
    submitLabel: "Update Status",
    fields: [
      { name: "asset", label: "Select Asset", type: "select", required: true, options: assetOptions },
      { name: "status", label: "New Status", type: "select", required: true, options: [{ value: "active", label: "Active" }, { value: "repair", label: "Under Repair" }, { value: "inactive", label: "Inactive" }, { value: "disposed", label: "Disposed" }] },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Reason for status change", colSpan: 2 },
    ],
  },
  "record-asset-revenue": {
    title: "Record Asset Revenue",
    description: "Record revenue generated from a church asset",
    type: "form",
    submitLabel: "Record Revenue",
    fields: [
      { name: "asset", label: "Select Asset", type: "select", required: true, options: assetOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
      { name: "account", label: "Bank Account", type: "select", options: bankAccountOptions },
      { name: "description", label: "Description", type: "textarea", placeholder: "Revenue details", colSpan: 2 },
    ],
  },
  "record-asset-expenses": {
    title: "Record Asset Expenses",
    description: "Record expenses for a church asset",
    type: "form",
    submitLabel: "Record Expense",
    fields: [
      { name: "asset", label: "Select Asset", type: "select", required: true, options: assetOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
      { name: "category", label: "Expense Category", type: "select", options: [{ value: "maintenance", label: "Maintenance" }, { value: "repair", label: "Repair" }, { value: "insurance", label: "Insurance" }, { value: "other", label: "Other" }] },
      { name: "description", label: "Description", type: "textarea", placeholder: "Expense details", colSpan: 2 },
    ],
  },
  // Harambee
  "record-harambee-classes": {
    title: "Record Harambee Class",
    description: "Define contribution classes for harambee",
    type: "form",
    submitLabel: "Add Class",
    fields: [
      { name: "name", label: "Class Name", type: "text", placeholder: "e.g. Class A", required: true },
      { name: "min_amount", label: "Minimum Amount (TZS)", type: "number", placeholder: "e.g. 500000", required: true },
      { name: "max_amount", label: "Maximum Amount (TZS)", type: "number", placeholder: "e.g. 1000000" },
      { name: "description", label: "Description", type: "textarea", placeholder: "Class description", colSpan: 2 },
    ],
  },
  "harambee-classes": {
    title: "Harambee Classes",
    description: "All defined harambee contribution classes",
    type: "table",
    columns: [
      { key: "name", label: "Class Name" },
      { key: "min_amount", label: "Min Amount" },
      { key: "max_amount", label: "Max Amount" },
      { key: "members", label: "Members" },
    ],
    data: genData(4, i => ({
      name: `Class ${["A", "B", "C", "D"][i]}`,
      min_amount: `TZS ${[500, 200, 100, 0][i]},000`,
      max_amount: `TZS ${[1000, 499, 199, 99][i]},000`,
      members: [45, 120, 280, 95][i],
    })),
    searchKeys: ["name"],
    actions: ["edit", "delete"],
  },
  "distribute-harambee": {
    title: "Distribute Harambee",
    description: "Distribute harambee targets to sub parishes",
    type: "form",
    submitLabel: "Distribute",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "sub_parish", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "amount", label: "Target Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "harambee-distribution": {
    title: "Harambee Distribution Status",
    description: "View distribution status across sub parishes",
    type: "table",
    columns: [
      { key: "sub_parish", label: "Sub Parish" },
      { key: "target", label: "Target" },
      { key: "distributed", label: "Distributed" },
      { key: "status", label: "Status", type: "badge", badgeColors: statusBadge },
    ],
    data: genData(4, i => ({
      sub_parish: subParishOptions[i].label,
      target: `TZS ${[12, 8, 15, 10][i]}M`,
      distributed: `TZS ${[12, 8, 15, 0][i]}M`,
      status: i === 3 ? "Not Distributed" : "Distributed",
    })),
    searchKeys: ["sub_parish"],
    actions: ["view", "edit"],
  },
  "record-harambee-target": {
    title: "Set Member Harambee Target",
    description: "Set a harambee target for a specific member",
    type: "form",
    submitLabel: "Set Target",
    statusPreview: harambeeStatusPreview,
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "class", label: "Class", type: "select", options: harambeeClassOptions },
      { name: "amount", label: "Target Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "upload-harambee-targets": {
    title: "Upload Harambee Targets",
    description: "Bulk upload member harambee targets from file",
    type: "form",
    submitLabel: "Upload File",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "file_note", label: "File Description", type: "text", placeholder: "Describe the upload" },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional notes", colSpan: 2 },
    ],
  },
  "record-harambee-contribution": {
    title: "Record Harambee Contribution",
    description: "Record a member's harambee contribution",
    type: "form",
    submitLabel: "Record Contribution",
    statusPreview: harambeeStatusPreview,
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
      { name: "payment_method", label: "Payment Method", type: "select", options: [{ value: "cash", label: "Cash" }, { value: "mobile", label: "Mobile Money" }, { value: "bank", label: "Bank Transfer" }] },
      { name: "reference", label: "Reference", type: "text", placeholder: "Transaction reference" },
    ],
  },
  "harambee-contribution": {
    title: "Harambee Contributions",
    description: "All recorded harambee contributions",
    type: "table",
    columns: [
      { key: "member", label: "Member" },
      { key: "harambee", label: "Harambee" },
      { key: "amount", label: "Amount" },
      { key: "date", label: "Date" },
      { key: "method", label: "Payment Method" },
    ],
    data: genData(25, i => ({
      member: memberOptions[i % 10].label,
      harambee: harambeeOptions[i % 4].label,
      amount: `TZS ${(i + 1) * 50},000`,
      date: `2025-${String(1 + (i % 12)).padStart(2, "0")}-${String(1 + i).padStart(2, "0")}`,
      method: ["Cash", "Mobile Money", "Bank Transfer"][i % 3],
    })),
    searchKeys: ["member", "harambee"],
    actions: ["view", "edit"],
  },
  "send-harambee-contribution-sms": {
    title: "Send Contribution SMS",
    description: "Send SMS receipts for harambee contributions",
    type: "form",
    submitLabel: "Send SMS",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "target", label: "Target", type: "select", required: true, options: [{ value: "all", label: "All Contributors" }, { value: "recent", label: "Recent Contributors (7 days)" }, { value: "specific", label: "Specific Member" }] },
      { name: "message", label: "SMS Message", type: "textarea", placeholder: "Enter message or use default template", colSpan: 2 },
    ],
  },
  "send-harambee-summary-message": {
    title: "Send Harambee Summary SMS",
    description: "Send summary SMS to all members",
    type: "form",
    submitLabel: "Send Summary",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "sub_parish", label: "Sub Parish", type: "select", options: [{ value: "all", label: "All Sub Parishes" }, ...subParishOptions] },
      { name: "message", label: "Summary Message", type: "textarea", placeholder: "Summary message template", colSpan: 2 },
    ],
  },
  "send-harambee-contribution-notification": {
    title: "Send Harambee Notification",
    description: "Send push notification about harambee",
    type: "form",
    submitLabel: "Send Notification",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "title", label: "Notification Title", type: "text", placeholder: "Enter title", required: true },
      { name: "target", label: "Target Audience", type: "select", required: true, options: [{ value: "all", label: "All Members" }, { value: "non_contrib", label: "Non-Contributors" }, { value: "partial", label: "Partial Contributors" }] },
      { name: "message", label: "Message", type: "textarea", placeholder: "Notification message", required: true, colSpan: 2 },
    ],
  },
  "generate-harambee-letter": {
    title: "Generate Harambee Letter",
    description: "Generate contribution letters for members",
    type: "form",
    submitLabel: "Generate Letters",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "sub_parish", label: "Sub Parish", type: "select", options: [{ value: "all", label: "All Sub Parishes" }, ...subParishOptions] },
      { name: "format", label: "Format", type: "select", required: true, options: [{ value: "pdf", label: "PDF" }, { value: "docx", label: "Word (DOCX)" }] },
    ],
  },
  "harambee-letter-status": {
    title: "Harambee Letter Status",
    description: "Track letter generation and delivery status",
    type: "table",
    columns: [
      { key: "member", label: "Member" },
      { key: "harambee", label: "Harambee" },
      { key: "generated", label: "Generated" },
      { key: "delivered", label: "Delivered" },
      { key: "status", label: "Status", type: "badge", badgeColors: statusBadge },
    ],
    data: genData(15, i => ({
      member: memberOptions[i % 10].label,
      harambee: harambeeOptions[i % 4].label,
      generated: `2025-${String(1 + (i % 6)).padStart(2, "0")}-15`,
      delivered: i % 3 === 0 ? "Not Delivered" : `2025-${String(1 + (i % 6)).padStart(2, "0")}-${16 + (i % 5)}`,
      status: i % 3 === 0 ? "Pending" : "Completed",
    })),
    searchKeys: ["member"],
    actions: ["view"],
  },
  "non-harambee-members": {
    title: "Non-Participating Members",
    description: "Members who have not contributed to harambee",
    type: "table",
    columns: [
      { key: "name", label: "Member Name" },
      { key: "sub_parish", label: "Sub Parish" },
      { key: "community", label: "Community" },
      { key: "phone", label: "Phone" },
    ],
    data: genData(18, i => ({
      name: memberOptions[i % 10].label,
      sub_parish: subParishOptions[i % 4].label,
      community: communityOptions[i % 4].label,
      phone: `07${String(10000000 + i * 333).slice(0, 8)}`,
    })),
    searchKeys: ["name", "sub_parish"],
    actions: ["view"],
  },
  "record-harambee-expenses": {
    title: "Record Harambee Expenses",
    description: "Record expenses related to harambee",
    type: "form",
    submitLabel: "Record Expense",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "description", label: "Description", type: "text", placeholder: "Expense description", required: true },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
      { name: "account", label: "Bank Account", type: "select", options: bankAccountOptions },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional details", colSpan: 2 },
    ],
  },
  // Harambee Groups
  "create-harambee-group": {
    title: "Create Harambee Group",
    description: "Create a new harambee group",
    type: "form",
    submitLabel: "Create Group",
    fields: [
      { name: "name", label: "Group Name", type: "text", placeholder: "Enter group name", required: true },
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "description", label: "Description", type: "textarea", placeholder: "Group description", colSpan: 2 },
    ],
  },
  "assign-member-to-group": {
    title: "Assign Member to Group",
    description: "Assign a member to a harambee group",
    type: "form",
    submitLabel: "Assign Member",
    fields: [
      { name: "group", label: "Select Group", type: "select", required: true, options: groupOptions },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
    ],
  },
  "harambee-groups": {
    title: "Harambee Groups",
    description: "All harambee groups and their members",
    type: "table",
    columns: [
      { key: "name", label: "Group Name" },
      { key: "harambee", label: "Harambee" },
      { key: "members", label: "Members" },
      { key: "total_contributed", label: "Total Contributed" },
    ],
    data: genData(6, i => ({
      name: groupOptions[i % 4].label,
      harambee: harambeeOptions[i % 4].label,
      members: [12, 18, 8, 22, 15, 10][i],
      total_contributed: `TZS ${[3.2, 5.1, 1.8, 6.5, 4.0, 2.5][i]}M`,
    })),
    searchKeys: ["name"],
    actions: ["view", "edit", "delete"],
  },
  // Harambee Exclusion
  "add-harambee-exclusion-reason": {
    title: "Add Harambee Exclusion Reason",
    description: "Define a reason for excluding members from harambee",
    type: "form",
    submitLabel: "Add Reason",
    fields: [
      { name: "reason", label: "Exclusion Reason", type: "text", placeholder: "Enter reason", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Details", colSpan: 2 },
    ],
  },
  "harambee-exclusions": {
    title: "Harambee Exclusion Reasons",
    description: "All harambee exclusion reasons",
    type: "table",
    columns: [
      { key: "reason", label: "Reason" },
      { key: "description", label: "Description" },
      { key: "excluded_count", label: "Members Excluded" },
    ],
    data: genData(4, i => ({
      reason: ["Elderly/Sick", "Financial Hardship", "Already Contributing Elsewhere", "New Member"][i],
      description: ["Members above 70 or with chronic illness", "Members facing financial difficulties", "Contributing to another harambee", "Members joined less than 6 months ago"][i],
      excluded_count: [15, 8, 5, 12][i],
    })),
    searchKeys: ["reason"],
    actions: ["edit", "delete"],
  },
  "exclude-church-member-from-harambee": {
    title: "Exclude Member from Harambee",
    description: "Exclude a member from harambee participation",
    type: "form",
    submitLabel: "Exclude Member",
    statusPreview: harambeeStatusPreview,
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "reason", label: "Reason", type: "select", required: true, options: [{ value: "elderly", label: "Elderly/Sick" }, { value: "financial", label: "Financial Hardship" }, { value: "other", label: "Already Contributing Elsewhere" }, { value: "new", label: "New Member" }] },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional notes", colSpan: 2 },
    ],
  },
  "excluded-church-members-from-harambee": {
    title: "Excluded Harambee Members",
    description: "Members excluded from harambee participation",
    type: "table",
    columns: [
      { key: "member", label: "Member" },
      { key: "harambee", label: "Harambee" },
      { key: "reason", label: "Reason" },
      { key: "date", label: "Date Excluded" },
    ],
    data: genData(12, i => ({
      member: memberOptions[i % 10].label,
      harambee: harambeeOptions[i % 4].label,
      reason: ["Elderly/Sick", "Financial Hardship", "Already Contributing Elsewhere", "New Member"][i % 4],
      date: `2025-${String(1 + (i % 6)).padStart(2, "0")}-${String(10 + i).padStart(2, "0")}`,
    })),
    searchKeys: ["member", "reason"],
    actions: ["view"],
  },
  // Envelope
  "set-annual-envelope-target": {
    title: "Set Annual Envelope Target",
    description: "Set the overall annual envelope target",
    type: "form",
    submitLabel: "Save Target",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "target", label: "Annual Target (TZS)", type: "number", placeholder: "e.g. 50000000", required: true },
    ],
  },
  "distribute-annual-envelope-target": {
    title: "Distribute Envelope Target",
    description: "Distribute envelope targets to sub parishes",
    type: "form",
    submitLabel: "Distribute",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "sub_parish", label: "Sub Parish", type: "select", required: true, options: subParishOptions },
      { name: "amount", label: "Target (TZS)", type: "number", placeholder: "Enter target", required: true },
    ],
  },
  "set-envelope-target": {
    title: "Set Member Envelope Target",
    description: "Set envelope contribution target for a member",
    type: "form",
    submitLabel: "Set Target",
    statusPreview: envelopeStatusPreview,
    fields: [
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "amount", label: "Target Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "record-envelope-contribution": {
    title: "Record Envelope Contribution",
    description: "Record a member's envelope contribution",
    type: "form",
    submitLabel: "Record Contribution",
    fields: [
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
      { name: "envelope_number", label: "Envelope Number", type: "text", placeholder: "e.g. Y001" },
    ],
  },
  "upload-envelope-data": {
    title: "Upload Envelope Data",
    description: "Bulk upload envelope contributions from file",
    type: "form",
    submitLabel: "Upload File",
    fields: [
      { name: "year", label: "Year", type: "select", required: true, options: [{ value: "2025", label: "2025" }, { value: "2026", label: "2026" }] },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Notes about this upload", colSpan: 2 },
    ],
  },
  "envelope-usage-summary": {
    title: "Download Envelope Usage Summary",
    description: "Download envelope usage summary report for a specific date",
    type: "form",
    submitLabel: "Download Report",
    fields: [
      { name: "report_date", label: "Select Date", type: "date", required: true },
    ],
  },
  // Reports
  "download-revenue-breakdown": {
    title: "Revenue Breakdown",
    description: "Generate revenue breakdown report",
    type: "form",
    submitLabel: "Generate Report",
    fields: [
      { name: "from_date", label: "From Date", type: "date", required: true },
      { name: "to_date", label: "To Date", type: "date", required: true },
      { name: "group_by", label: "Group By", type: "select", options: [{ value: "stream", label: "Revenue Stream" }, { value: "sub_parish", label: "Sub Parish" }, { value: "month", label: "Month" }] },
      { name: "format", label: "Format", type: "select", required: true, options: [{ value: "pdf", label: "PDF" }, { value: "xlsx", label: "Excel" }, { value: "csv", label: "CSV" }] },
    ],
  },
  "download-revenue-statement": {
    title: "Revenue Statement",
    description: "Generate comprehensive revenue statement",
    type: "form",
    submitLabel: "Generate Statement",
    fields: [
      { name: "from_date", label: "From Date", type: "date", required: true },
      { name: "to_date", label: "To Date", type: "date", required: true },
      { name: "account", label: "Bank Account", type: "select", options: [{ value: "all", label: "All Accounts" }, ...bankAccountOptions] },
      { name: "format", label: "Format", type: "select", required: true, options: [{ value: "pdf", label: "PDF" }, { value: "xlsx", label: "Excel" }] },
    ],
  },
  "head-parish-harambee-report": {
    title: "Head Parish Harambee Report",
    description: "Comprehensive harambee report",
    type: "table",
    columns: [
      { key: "harambee", label: "Harambee" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
      { key: "contributors", label: "Contributors" },
    ],
    data: genData(4, i => ({
      harambee: harambeeOptions[i].label,
      target: `TZS ${[50, 25, 15, 8][i]}M`,
      collected: `TZS ${[33.5, 18, 10, 5][i]}M`,
      progress: [67, 72, 67, 63][i],
      contributors: [450, 280, 190, 120][i],
    })),
    searchKeys: ["harambee"],
    actions: ["view"],
  },
  "harambee-contribution-summary": {
    title: "Contribution Summary",
    description: "Summary of all harambee contributions",
    type: "table",
    columns: [
      { key: "sub_parish", label: "Sub Parish" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "variance", label: "Variance" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      sub_parish: subParishOptions[i].label,
      target: `TZS ${[15, 10, 18, 12][i]}M`,
      collected: `TZS ${[10, 7.5, 14, 8][i]}M`,
      variance: `TZS ${[5, 2.5, 4, 4][i]}M`,
      progress: [67, 75, 78, 67][i],
    })),
    searchKeys: ["sub_parish"],
    actions: ["view"],
  },
  "harambee-contribution-report": {
    title: "Contribution Report",
    description: "Detailed contribution report by member",
    type: "table",
    columns: [
      { key: "member", label: "Member" },
      { key: "class", label: "Class" },
      { key: "target", label: "Target" },
      { key: "contributed", label: "Contributed" },
      { key: "balance", label: "Balance" },
    ],
    data: genData(20, i => ({
      member: memberOptions[i % 10].label,
      class: ["A", "B", "C", "D"][i % 4],
      target: `TZS ${[500, 200, 100, 50][i % 4]},000`,
      contributed: `TZS ${[350, 150, 80, 30][i % 4]},000`,
      balance: `TZS ${[150, 50, 20, 20][i % 4]},000`,
    })),
    searchKeys: ["member"],
    actions: ["view"],
  },
  "harambee-groups-report": {
    title: "Harambee Groups Report",
    description: "Report by harambee groups",
    type: "table",
    columns: [
      { key: "group", label: "Group" },
      { key: "members", label: "Members" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      group: groupOptions[i].label,
      members: [12, 18, 8, 22][i],
      target: `TZS ${[6, 9, 4, 11][i]}M`,
      collected: `TZS ${[4, 7, 2.5, 8][i]}M`,
      progress: [67, 78, 63, 73][i],
    })),
    searchKeys: ["group"],
    actions: ["view"],
  },
  "harambee-community-report": {
    title: "Harambee Community Report",
    description: "Report by community",
    type: "table",
    columns: [
      { key: "community", label: "Community" },
      { key: "members", label: "Members" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      community: communityOptions[i].label,
      members: [45, 38, 52, 30][i],
      target: `TZS ${[12, 10, 15, 8][i]}M`,
      collected: `TZS ${[8, 7, 11, 5][i]}M`,
      progress: [67, 70, 73, 63][i],
    })),
    searchKeys: ["community"],
    actions: ["view"],
  },
  "contribution-report-by-class": {
    title: "Contribution Report by Class",
    description: "Harambee contributions grouped by class",
    type: "table",
    columns: [
      { key: "class", label: "Class" },
      { key: "members", label: "Members" },
      { key: "total_target", label: "Total Target" },
      { key: "total_collected", label: "Total Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      class: `Class ${["A", "B", "C", "D"][i]}`,
      members: [45, 120, 280, 95][i],
      total_target: `TZS ${[22.5, 24, 28, 4.75][i]}M`,
      total_collected: `TZS ${[16, 18, 20, 3][i]}M`,
      progress: [71, 75, 71, 63][i],
    })),
    searchKeys: ["class"],
    actions: ["view"],
  },
  "harambee-letters-report": {
    title: "Harambee Letters Report",
    description: "Status of generated harambee letters",
    type: "table",
    columns: [
      { key: "sub_parish", label: "Sub Parish" },
      { key: "total_letters", label: "Total Letters" },
      { key: "delivered", label: "Delivered" },
      { key: "pending", label: "Pending" },
    ],
    data: genData(4, i => ({
      sub_parish: subParishOptions[i].label,
      total_letters: [120, 85, 150, 95][i],
      delivered: [95, 70, 125, 78][i],
      pending: [25, 15, 25, 17][i],
    })),
    searchKeys: ["sub_parish"],
    actions: ["view"],
  },
  "clerks-harambee-report": {
    title: "Clerks Harambee Report",
    description: "Harambee collection report by clerks",
    type: "table",
    columns: [
      { key: "clerk", label: "Clerk Name" },
      { key: "sub_parish", label: "Sub Parish" },
      { key: "collections", label: "Collections" },
      { key: "total_amount", label: "Total Amount" },
    ],
    data: genData(8, i => ({
      clerk: memberOptions[i % 10].label,
      sub_parish: subParishOptions[i % 4].label,
      collections: [25, 18, 32, 15, 28, 20, 35, 22][i],
      total_amount: `TZS ${[3.2, 2.1, 4.5, 1.8, 3.8, 2.5, 5.2, 2.8][i]}M`,
    })),
    searchKeys: ["clerk"],
    actions: ["view"],
  },
  // Revenue & Budget Reports
  "ogo-report": {
    title: "OGO Report",
    description: "Overall budget vs actuals report",
    type: "table",
    columns: [
      { key: "category", label: "Category" },
      { key: "budget", label: "Budget" },
      { key: "actual", label: "Actual" },
      { key: "variance", label: "Variance" },
    ],
    data: genData(6, i => ({
      category: ["Revenue", "Offerings", "Tithes", "Expenses", "Salaries", "Operations"][i],
      budget: `TZS ${[150, 60, 40, 100, 45, 30][i]}M`,
      actual: `TZS ${[135, 55, 38, 95, 44, 28][i]}M`,
      variance: `${["-10%", "-8%", "-5%", "+5%", "+2%", "+7%"][i]}`,
    })),
    searchKeys: ["category"],
    actions: ["view"],
  },
  "revenue-group-report": {
    title: "Revenue Groups Report",
    description: "Revenue performance by group",
    type: "table",
    columns: [
      { key: "group", label: "Revenue Group" },
      { key: "streams", label: "Streams" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      group: ["Offerings", "Tithes", "Special Collections", "Other"][i],
      streams: [5, 2, 3, 2][i],
      target: `TZS ${[60, 40, 30, 20][i]}M`,
      collected: `TZS ${[48, 35, 22, 15][i]}M`,
      progress: [80, 88, 73, 75][i],
    })),
    searchKeys: ["group"],
    actions: ["view"],
  },
  "expense-group-report": {
    title: "Expense Groups Report",
    description: "Expense performance by group",
    type: "table",
    columns: [
      { key: "group", label: "Expense Group" },
      { key: "budget", label: "Budget" },
      { key: "spent", label: "Spent" },
      { key: "remaining", label: "Remaining" },
      { key: "progress", label: "Utilization", type: "progress" },
    ],
    data: genData(5, i => ({
      group: expenseGroupOptions[i].label,
      budget: `TZS ${[15, 8, 45, 5, 10][i]}M`,
      spent: `TZS ${[12, 9, 40, 3.5, 7][i]}M`,
      remaining: `TZS ${[3, -1, 5, 1.5, 3][i]}M`,
      progress: [80, 112, 89, 70, 70][i],
    })),
    searchKeys: ["group"],
    actions: ["view"],
  },
  // Third Parties
  "register-payment-gateway-wallet": {
    title: "Register Payment Wallet",
    description: "Add a new mobile money or payment wallet",
    type: "form",
    submitLabel: "Register Wallet",
    fields: [
      { name: "provider", label: "Provider", type: "select", required: true, options: [{ value: "mpesa", label: "M-Pesa" }, { value: "tigopesa", label: "Tigo Pesa" }, { value: "airtelmoney", label: "Airtel Money" }, { value: "halopesa", label: "Halo Pesa" }] },
      { name: "wallet_number", label: "Wallet Number", type: "tel", placeholder: "Enter wallet number", required: true },
      { name: "name", label: "Wallet Name", type: "text", placeholder: "e.g. Parish M-Pesa" },
      { name: "account", label: "Linked Bank Account", type: "select", options: bankAccountOptions },
    ],
  },
  "record-sms-api-info": {
    title: "Record SMS API Info",
    description: "Configure SMS gateway API settings",
    type: "form",
    submitLabel: "Save API Settings",
    fields: [
      { name: "provider", label: "SMS Provider", type: "select", required: true, options: [{ value: "beem", label: "Beem Africa" }, { value: "nextsms", label: "NextSMS" }, { value: "twilio", label: "Twilio" }] },
      { name: "api_key", label: "API Key", type: "text", placeholder: "Enter API key", required: true },
      { name: "secret", label: "API Secret", type: "text", placeholder: "Enter API secret", required: true },
      { name: "sender_id", label: "Sender ID", type: "text", placeholder: "e.g. KANISA" },
    ],
  },
};

// ============ SUB PARISH EXTRA PAGES ============
export const subParishPages: Record<string, PageConfig> = {
  "add-revenue-stream": {
    title: "Add Revenue Stream",
    description: "Create a new revenue stream",
    type: "form",
    submitLabel: "Add Stream",
    fields: [
      { name: "name", label: "Stream Name", type: "text", placeholder: "Enter name", required: true },
      { name: "account", label: "Account", type: "select", options: [{ value: "main", label: "Main Account" }, { value: "building", label: "Building Fund" }] },
    ],
  },
  "record-revenue": {
    title: "Record Revenue",
    description: "Record new revenue",
    type: "form",
    submitLabel: "Record Revenue",
    fields: [
      { name: "stream", label: "Revenue Stream", type: "select", required: true, options: revenueStreamOptions.slice(0, 4) },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
    ],
  },
  "record-debit": {
    title: "Record Debit",
    description: "Record a new debit",
    type: "form",
    submitLabel: "Record Debit",
    fields: [
      { name: "description", label: "Description", type: "text", placeholder: "Enter description", required: true },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
    ],
  },
  "record-harambee": {
    title: "Record Harambee",
    description: "Record a new harambee program",
    type: "form",
    submitLabel: "Record Harambee",
    fields: [
      { name: "description", label: "Description", type: "text", placeholder: "Enter description", required: true },
      { name: "target", label: "Target Amount (TZS)", type: "number", placeholder: "Enter target", required: true },
      { name: "from_date", label: "Start Date", type: "date", required: true },
      { name: "to_date", label: "End Date", type: "date", required: true },
    ],
  },
  "distribute-harambee": {
    title: "Distribute Harambee",
    description: "Distribute harambee targets to communities",
    type: "form",
    submitLabel: "Distribute",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions.slice(0, 3) },
      { name: "community", label: "Community", type: "select", required: true, options: communityOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "harambee-distribution": {
    title: "Harambee Distribution Status",
    description: "View harambee distribution status",
    type: "table",
    columns: [
      { key: "community", label: "Community" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      community: communityOptions[i].label,
      target: `TZS ${[3, 2, 4, 2.5][i]}M`,
      collected: `TZS ${[2, 1.5, 3, 1.5][i]}M`,
      progress: [67, 75, 75, 60][i],
    })),
    searchKeys: ["community"],
    actions: ["view"],
  },
  "record-harambee-target": {
    title: "Set Member Harambee Target",
    description: "Set harambee target for a member",
    type: "form",
    submitLabel: "Set Target",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions.slice(0, 3) },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "amount", label: "Target (TZS)", type: "number", placeholder: "Enter amount", required: true },
    ],
  },
  "record-harambee-contribution": {
    title: "Record Harambee Contribution",
    description: "Record a member's harambee contribution",
    type: "form",
    submitLabel: "Record Contribution",
    fields: [
      { name: "harambee", label: "Select Harambee", type: "select", required: true, options: harambeeOptions.slice(0, 3) },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
    ],
  },
  "harambee-contribution": {
    title: "Harambee Contributions",
    description: "All harambee contributions",
    type: "table",
    columns: [
      { key: "member", label: "Member" },
      { key: "amount", label: "Amount" },
      { key: "date", label: "Date" },
    ],
    data: genData(15, i => ({
      member: memberOptions[i % 10].label,
      amount: `TZS ${(i + 1) * 30},000`,
      date: `2025-${String(1 + (i % 6)).padStart(2, "0")}-${String(5 + i).padStart(2, "0")}`,
    })),
    searchKeys: ["member"],
    actions: ["view", "edit"],
  },
  "harambee-community-report": {
    title: "Community Harambee Report",
    description: "Harambee report by community",
    type: "table",
    columns: [
      { key: "community", label: "Community" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      community: communityOptions[i].label,
      target: `TZS ${[3, 2, 4, 2.5][i]}M`,
      collected: `TZS ${[2, 1.5, 3, 1.5][i]}M`,
      progress: [67, 75, 75, 60][i],
    })),
    searchKeys: ["community"],
    actions: ["view"],
  },
  "harambee-groups-report": {
    title: "Harambee Groups Report",
    description: "Report by harambee groups",
    type: "table",
    columns: [
      { key: "group", label: "Group" },
      { key: "members", label: "Members" },
      { key: "collected", label: "Collected" },
    ],
    data: genData(4, i => ({
      group: groupOptions[i].label,
      members: [8, 12, 5, 15][i],
      collected: `TZS ${[1.5, 2.5, 1, 3][i]}M`,
    })),
    searchKeys: ["group"],
    actions: ["view"],
  },
  "head-parish-harambee-report": {
    title: "Head Parish Harambee Report",
    description: "Report for head parish level",
    type: "table",
    columns: [
      { key: "harambee", label: "Harambee" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(3, i => ({
      harambee: ["Church Building", "Youth Center", "Road Repair"][i],
      target: `TZS ${[10, 5, 3][i]}M`,
      collected: `TZS ${[6, 3, 1.5][i]}M`,
      progress: [60, 60, 50][i],
    })),
    searchKeys: ["harambee"],
    actions: ["view"],
  },
  "harambee-contribution-summary": {
    title: "Contribution Summary",
    description: "Summary of all contributions",
    type: "table",
    columns: [
      { key: "community", label: "Community" },
      { key: "target", label: "Target" },
      { key: "collected", label: "Collected" },
      { key: "progress", label: "Progress", type: "progress" },
    ],
    data: genData(4, i => ({
      community: communityOptions[i].label,
      target: `TZS ${[3, 2, 4, 2.5][i]}M`,
      collected: `TZS ${[2, 1.5, 3, 1.5][i]}M`,
      progress: [67, 75, 75, 60][i],
    })),
    searchKeys: ["community"],
    actions: ["view"],
  },
  "create-harambee-group": {
    title: "Create Harambee Group",
    description: "Create a new harambee group",
    type: "form",
    submitLabel: "Create Group",
    fields: [
      { name: "name", label: "Group Name", type: "text", placeholder: "Enter name", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Description", colSpan: 2 },
    ],
  },
  "assign-member-to-group": {
    title: "Assign Member to Group",
    description: "Assign a member to a harambee group",
    type: "form",
    submitLabel: "Assign Member",
    fields: [
      { name: "group", label: "Select Group", type: "select", required: true, options: groupOptions },
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
    ],
  },
  "harambee-groups": {
    title: "Harambee Groups",
    description: "All harambee groups",
    type: "table",
    columns: [
      { key: "name", label: "Group Name" },
      { key: "members", label: "Members" },
      { key: "collected", label: "Total Collected" },
    ],
    data: genData(4, i => ({
      name: groupOptions[i].label,
      members: [8, 12, 5, 15][i],
      collected: `TZS ${[1.5, 2.5, 1, 3][i]}M`,
    })),
    searchKeys: ["name"],
    actions: ["view", "edit", "delete"],
  },
  "exclude-member-from-harambee": {
    title: "Exclude Member from Harambee",
    description: "Exclude a member from harambee",
    type: "form",
    submitLabel: "Exclude Member",
    fields: [
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "reason", label: "Reason", type: "select", required: true, options: exclusionReasonOptions },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional notes", colSpan: 2 },
    ],
  },
  "set-envelope-target": {
    title: "Set Envelope Target",
    description: "Set envelope target for a member",
    type: "form",
    submitLabel: "Set Target",
    fields: [
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "amount", label: "Target (TZS)", type: "number", placeholder: "Enter target", required: true },
    ],
  },
  "record-envelope-contribution": {
    title: "Record Envelope Contribution",
    description: "Record a member's envelope contribution",
    type: "form",
    submitLabel: "Record Contribution",
    fields: [
      { name: "member", label: "Select Member", type: "select", required: true, options: memberOptions },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "date", label: "Date", type: "date", required: true },
    ],
  },
  "create-expense-groups": {
    title: "Create Expense Groups",
    description: "Define expense group categories",
    type: "form",
    submitLabel: "Create Group",
    fields: [
      { name: "name", label: "Group Name", type: "text", placeholder: "Enter name", required: true },
      { name: "description", label: "Description", type: "textarea", placeholder: "Description", colSpan: 2 },
    ],
  },
  "record-expense-names": {
    title: "Record Expense Names",
    description: "Add expense line items",
    type: "form",
    submitLabel: "Add Expense Name",
    fields: [
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "name", label: "Expense Name", type: "text", placeholder: "Enter name", required: true },
    ],
  },
  "set-expense-budget": {
    title: "Set Expense Budget",
    description: "Allocate expense budgets",
    type: "form",
    submitLabel: "Set Budget",
    fields: [
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "amount", label: "Budget (TZS)", type: "number", placeholder: "Enter budget", required: true },
    ],
  },
  "make-expense-request": {
    title: "Make Expense Request",
    description: "Submit a new expense request",
    type: "form",
    submitLabel: "Submit Request",
    fields: [
      { name: "group", label: "Expense Group", type: "select", required: true, options: expenseGroupOptions },
      { name: "description", label: "Description", type: "text", placeholder: "Enter description", required: true },
      { name: "amount", label: "Amount (TZS)", type: "number", placeholder: "Enter amount", required: true },
      { name: "notes", label: "Notes", type: "textarea", placeholder: "Additional notes", colSpan: 2 },
    ],
  },
};
