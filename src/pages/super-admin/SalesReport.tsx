import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "month", label: "Month" },
  { key: "subscriptions", label: "Subscriptions" },
  { key: "revenue", label: "Revenue" },
  { key: "growth", label: "Growth" },
];

const data = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  month: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][i] + " 2025",
  subscriptions: 80 + i * 12,
  revenue: `TZS ${(8 + i) * 1.5}M`,
  growth: `+${3 + (i % 5)}%`,
}));

export default function SalesReport() {
  return (
    <DataTable
      title="Sales Report"
      description="Monthly sales and subscription data"
      columns={columns}
      data={data}
      searchPlaceholder="Search month..."
      searchKeys={["month"]}
      actions={["view"]}
    />
  );
}
