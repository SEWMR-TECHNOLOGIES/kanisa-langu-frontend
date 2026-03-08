import DataTable from "../../components/head-parish/DataTable";
import { mockChoirs } from "../../data/headParishMockData";

export default function ChurchChoirs() {
  return (
    <DataTable
      title="Church Choirs"
      description="Manage all church choirs and their details"
      columns={[
        { key: "name", label: "Choir Name" },
        { key: "members_count", label: "Members" },
        { key: "leader", label: "Leader" },
      ]}
      data={mockChoirs}
      searchPlaceholder="Search choirs..."
      searchKeys={["name", "leader"]}
    />
  );
}
