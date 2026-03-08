import DataTable from "../../components/head-parish/DataTable";
import { mockSPExpenseRequests } from "../../data/subParishMockData";

export default function SPExpenseRequests() {
  return (
    <DataTable
      title="Expense Requests"
      description="View and manage expense requests"
      columns={[
        { key: "description", label: "Description" },
        { key: "amount", label: "Amount" },
        { key: "requested_by", label: "Requested By" },
        { key: "date", label: "Date" },
        { key: "status", label: "Status", render: (r: any) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-semibold ${
            r.status === "Approved" ? "bg-admin-success/10 text-admin-success" :
            r.status === "Rejected" ? "bg-destructive/10 text-destructive" :
            "bg-admin-warning/10 text-admin-warning"
          }`}>{r.status}</span>
        )},
      ]}
      data={mockSPExpenseRequests}
      searchPlaceholder="Search..."
      searchKeys={["description", "requested_by"]}
      actions={["view", "edit"]}
    />
  );
}
