import DataTable from "../../components/head-parish/DataTable";
import { mockGroups } from "../../data/headParishMockData";

export default function Groups() {
  return (
    <DataTable
      title="Manage Groups"
      description="View and manage all church groups"
      columns={[
        { key: "name", label: "Group Name" },
        { key: "description", label: "Description" },
      ]}
      data={mockGroups}
      searchPlaceholder="Search groups..."
      searchKeys={["name"]}
    />
  );
}
