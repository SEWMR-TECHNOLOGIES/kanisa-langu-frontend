import DataTable from "../../components/head-parish/DataTable";
import { mockRevenueStreams } from "../../data/headParishMockData";

export default function RevenueStreams() {
  return (
    <DataTable
      title="Manage Revenue Streams"
      description="View and manage all revenue streams"
      columns={[
        { key: "name", label: "Revenue Stream Name" },
        { key: "account_name", label: "Account Name" },
      ]}
      data={mockRevenueStreams}
      searchPlaceholder="Search revenue streams..."
      searchKeys={["name"]}
    />
  );
}
