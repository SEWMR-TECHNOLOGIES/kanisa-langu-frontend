import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "diocese", label: "Diocese" },
  { key: "sms_sent", label: "SMS Sent" },
  { key: "sms_cost", label: "SMS Cost" },
  { key: "last_sent", label: "Last Sent" },
];

const data = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  diocese: `Diocese of ${["Moshi", "Arusha", "Dodoma", "Iringa", "Mbeya", "Dar es Salaam", "Tanga", "Morogoro", "Bukoba", "Mwanza", "Kigoma", "Songea"][i]}`,
  sms_sent: (i + 1) * 450,
  sms_cost: `TZS ${(i + 1) * 22},500`,
  last_sent: `2025-${String(1 + (i % 12)).padStart(2, "0")}-${String(10 + i).padStart(2, "0")}`,
}));

export default function SMSUsageReport() {
  return (
    <DataTable
      title="SMS Usage Report"
      description="SMS usage statistics by diocese"
      columns={columns}
      data={data}
      searchPlaceholder="Search diocese..."
      searchKeys={["diocese"]}
      actions={["view"]}
    />
  );
}
