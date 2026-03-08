import DataTable from "../../components/head-parish/DataTable";
import { mockSPRevenueStreams } from "../../data/subParishMockData";

export default function SPRevenueStreams() {
  return (
    <DataTable
      title="Revenue Streams"
      description="View and manage revenue streams"
      columns={[
        { key: "name", label: "Revenue Stream" },
        { key: "account", label: "Account" },
      ]}
      data={mockSPRevenueStreams}
      searchPlaceholder="Search..."
      searchKeys={["name"]}
      actions={["edit", "delete"]}
    />
  );
}
