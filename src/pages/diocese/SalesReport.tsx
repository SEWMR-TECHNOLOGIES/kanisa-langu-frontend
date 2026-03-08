import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "head_parish", label: "Head Parish" },
  { key: "plan", label: "Plan" },
  { key: "amount", label: "Amount" },
  { key: "date", label: "Date" },
];

const data = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  head_parish: `Usharika wa ${["Moshi", "Hai", "Rombo", "Same", "Mwanga"][i % 5]}`,
  plan: ["Annual", "Monthly", "Quarterly"][i % 3],
  amount: `TZS ${(i + 1) * 200},000`,
  date: `2025-${String(1 + (i % 12)).padStart(2, "0")}-01`,
}));

export default function SalesReport() {
  return (
    <DataTable
      title="Sales Report"
      description="Sales and subscription reports"
      columns={columns}
      data={data}
      searchPlaceholder="Search..."
      searchKeys={["head_parish", "plan"]}
      actions={["view"]}
    />
  );
}
