import DataTable from "../../components/head-parish/DataTable";
import { mockExpenseRequests } from "../../data/headParishMockData";

export default function ExpenseRequests() {
  return (
    <DataTable
      title="Expense Requests"
      description="View and manage all expense requests"
      columns={[
        { key: "description", label: "Description" },
        { key: "amount", label: "Amount", render: (r) => <span className="font-medium">TZS {Number(r.amount).toLocaleString()}</span> },
        { key: "requested_by", label: "Requested By" },
        { key: "date", label: "Date" },
        { key: "status", label: "Status", render: (r) => (
          <span className={`px-2.5 py-1 rounded-full text-[11px] font-medium ${
            r.status === "Approved" ? "bg-admin-success/10 text-admin-success" :
            r.status === "Rejected" ? "bg-destructive/10 text-destructive" :
            "bg-admin-warning/10 text-admin-warning"
          }`}>
            {r.status}
          </span>
        )},
      ]}
      data={mockExpenseRequests}
      searchPlaceholder="Search expense requests..."
      searchKeys={["description", "requested_by"]}
      actions={["view", "edit", "delete"]}
    />
  );
}
