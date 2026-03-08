import DataTable from "../../components/head-parish/DataTable";
import { mockDebits } from "../../data/headParishMockData";

export default function Debits() {
  return (
    <DataTable
      title="Debits & Loans"
      description="View all debits and loans"
      columns={[
        { key: "description", label: "Description" },
        { key: "amount", label: "Amount", render: (r) => <span className="font-medium text-admin-accent">TZS {Number(r.amount).toLocaleString()}</span> },
        { key: "creditor", label: "Creditor" },
        { key: "date", label: "Date" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${r.status === "Paid" ? "bg-admin-success/10 text-admin-success" : "bg-admin-warning/10 text-admin-warning"}`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockDebits}
      searchPlaceholder="Search debits..."
      searchKeys={["description", "creditor"]}
      actions={["edit", "delete"]}
    />
  );
}
