import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "head_parish", label: "Head Parish" },
  { key: "sent", label: "SMS Sent" },
  { key: "cost", label: "Cost" },
  { key: "month", label: "Month" },
];

const data = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  head_parish: `Usharika wa ${["Moshi", "Hai", "Rombo", "Same", "Mwanga", "Siha"][i % 6]}`,
  sent: 50 + i * 15,
  cost: `TZS ${(50 + i * 15) * 25}`,
  month: `2025-${String(1 + i).padStart(2, "0")}`,
}));

export default function SMSUsageReport() {
  return (
    <DataTable
      title="SMS Usage Report"
      description="SMS usage statistics by head parish"
      columns={columns}
      data={data}
      searchPlaceholder="Search..."
      searchKeys={["head_parish"]}
      actions={["view"]}
    />
  );
}
