import DataTable from "../../components/head-parish/DataTable";

const columns = [
  { key: "diocese", label: "Diocese" },
  { key: "total_payments", label: "Total Payments" },
  { key: "total_amount", label: "Total Amount" },
  { key: "last_payment", label: "Last Payment" },
];

const data = Array.from({ length: 12 }, (_, i) => ({
  id: i + 1,
  diocese: `Diocese of ${["Moshi", "Arusha", "Dodoma", "Iringa", "Mbeya", "Dar es Salaam", "Tanga", "Morogoro", "Bukoba", "Mwanza", "Kigoma", "Songea"][i]}`,
  total_payments: (i + 1) * 8,
  total_amount: `TZS ${(i + 1) * 2},500,000`,
  last_payment: `2025-${String(1 + (i % 12)).padStart(2, "0")}-15`,
}));

export default function PaymentReports() {
  return (
    <DataTable
      title="Payment Reports"
      description="Payment summary by diocese"
      columns={columns}
      data={data}
      searchPlaceholder="Search diocese..."
      searchKeys={["diocese"]}
      actions={["view"]}
    />
  );
}
