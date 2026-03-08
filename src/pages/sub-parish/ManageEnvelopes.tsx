import DataTable from "../../components/head-parish/DataTable";
import { mockSPEnvelopes } from "../../data/subParishMockData";

export default function SPManageEnvelopes() {
  return (
    <DataTable
      title="Manage Envelopes"
      description="View and manage member envelope contributions"
      columns={[
        { key: "member", label: "Member" },
        { key: "envelope_number", label: "Envelope #" },
        { key: "target", label: "Target" },
        { key: "contributed", label: "Contributed" },
        { key: "balance", label: "Balance" },
      ]}
      data={mockSPEnvelopes}
      searchPlaceholder="Search envelopes..."
      searchKeys={["member", "envelope_number"]}
      actions={["view", "edit"]}
    />
  );
}
