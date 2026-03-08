import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "head_parish", label: "Head Parish" },
  { key: "sent", label: "SMS Sent" },
  { key: "cost", label: "Cost" },
  { key: "month", label: "Month" },
];

const data = Array.from({ length: 10 }, (_, i) => ({
  id: i + 1,
  head_parish: `Usharika wa ${["Moshi", "Hai", "Rombo", "Same", "Mwanga"][i % 5]}`,
  sent: 40 + i * 12,
  cost: `TZS ${(40 + i * 12) * 25}`,
  month: `2025-${String(1 + i).padStart(2, "0")}`,
}));

export default function ProvinceSMSUsageReport() {
  return (
    <DataTable
      title="SMS Usage Report"
      description="SMS usage statistics"
      columns={columns}
      data={data}
      searchPlaceholder="Search..."
      searchKeys={["head_parish"]}
      actions={["view"]}
    />
  );
}
