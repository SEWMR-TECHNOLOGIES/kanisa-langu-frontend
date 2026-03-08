import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "id", label: "ID" },
  { key: "head_parish", label: "Head Parish" },
  { key: "amount", label: "Amount" },
  { key: "date", label: "Date" },
  { key: "status", label: "Status", render: (row: any) => (
    <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${row.status === "Completed" ? "bg-admin-success/10 text-admin-success" : "bg-admin-warning/10 text-admin-warning"}`}>{row.status}</span>
  )},
];

const data = Array.from({ length: 15 }, (_, i) => ({
  id: i + 1,
  head_parish: `Usharika wa ${["Moshi", "Hai", "Rombo", "Same", "Mwanga"][i % 5]}`,
  amount: `TZS ${(i + 1) * 150},000`,
  date: `2025-${String(1 + (i % 12)).padStart(2, "0")}-${String(5 + i).padStart(2, "0")}`,
  status: i % 3 === 0 ? "Pending" : "Completed",
}));

export default function ManagePayments() {
  return (
    <DataTable
      title="Manage Payments"
      description="All payment records"
      columns={columns}
      data={data}
      searchPlaceholder="Search payments..."
      searchKeys={["head_parish"]}
      actions={["view", "edit"]}
    />
  );
}
