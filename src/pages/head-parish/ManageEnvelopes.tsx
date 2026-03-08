import DataTable from "../../components/head-parish/DataTable";
import { mockEnvelopes } from "../../data/headParishMockData";

export default function ManageEnvelopes() {
  return (
    <DataTable
      title="Manage Envelopes"
      description="Track envelope contributions and targets"
      columns={[
        { key: "member_name", label: "Member Name" },
        { key: "envelope_number", label: "Envelope No." },
        { key: "target", label: "Target", render: (r) => `TZS ${Number(r.target).toLocaleString()}` },
        { key: "contributed", label: "Contributed", render: (r) => <span className="text-admin-success">TZS {Number(r.contributed).toLocaleString()}</span> },
        { key: "balance", label: "Balance", render: (r) => <span className="text-admin-warning">TZS {Number(r.balance).toLocaleString()}</span> },
      ]}
      data={mockEnvelopes}
      searchPlaceholder="Search by member or envelope number..."
      searchKeys={["member_name", "envelope_number"]}
      actions={["edit"]}
    />
  );
}
