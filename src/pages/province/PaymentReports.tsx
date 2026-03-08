import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "head_parish", label: "Head Parish" },
  { key: "total_paid", label: "Total Paid" },
  { key: "last_payment", label: "Last Payment" },
  { key: "status", label: "Status", render: (row: any) => (
    <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${row.status === "Up to Date" ? "bg-admin-success/10 text-admin-success" : "bg-admin-warning/10 text-admin-warning"}`}>{row.status}</span>
  )},
];

const data = Array.from({ length: 8 }, (_, i) => ({
  id: i + 1,
  head_parish: `Usharika wa ${["Moshi", "Hai", "Rombo", "Same"][i % 4]}`,
  total_paid: `TZS ${(i + 1) * 500},000`,
  last_payment: `2025-${String(1 + (i % 6)).padStart(2, "0")}-15`,
  status: i % 3 === 2 ? "Overdue" : "Up to Date",
}));

export default function ProvincePaymentReports() {
  return (
    <DataTable
      title="Payment Reports"
      description="Payment status reports"
      columns={columns}
      data={data}
      searchPlaceholder="Search..."
      searchKeys={["head_parish"]}
      actions={["view"]}
    />
  );
}
